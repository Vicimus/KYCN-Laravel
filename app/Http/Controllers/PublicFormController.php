<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Models\Submission;
use App\Services\SubmissionNotifier;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Random\RandomException;

class PublicFormController extends Controller
{
    public function show(Request $request): View
    {
        $code = (string) $request->query('d', '');
        $dealer = $code !== '' ? Dealer::where('code', $code)->first() : null;

        $dealerOptions = Dealer::query()
            ->whereNotNull('know_your_car_date')
            ->whereDate('know_your_car_date', '>=', now()->toDateString())
            ->orderBy('know_your_car_date')
            ->orderBy('name')
            ->get();

        $logo = $dealer?->dealership_logo ?: 'https://vicimus.com/wp-content/uploads/2023/08/bumper.svg';

        return view('public.form', [
            'dealer' => $dealer,
            'logo' => $logo,
            'dealerOptions' => $dealerOptions,
        ]);
    }

    /**
     * @throws RandomException
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'dealership_name' => 'nullable|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'number_of_attendees' => 'required|in:1,2',
            'email' => 'required|email',
            'phone' => 'required|string|max:30',
            'vehicle_purchased' => 'nullable|date',
            'know_your_car_date' => 'nullable|date',
        ]);

        $dealer = null;
        if ($request->query('d')) {
            $dealer = Dealer::where('code', $request->query('d'))->first();
        }

        $qs = [];
        if ($request->filled('d')) {
            $qs['d'] = $request->query('d');
        }

        if (!$dealer && !empty($data['dealership_name'])) {
            $dealer = Dealer::firstOrCreate(
                ['name' => trim($data['dealership_name'])],
                ['code' => Str::upper(preg_replace('/[^A-Za-z0-9]+/', '', $data['dealership_name'])) ?: 'DEALER'.random_int(1000, 9999)]
            );
        }

        if (!$dealer) {
            $dealer = Dealer::orderBy('id')->first();
        }

        $selectedKycnDate = $request->filled('know_your_car_date')
            ? Carbon::parse($request->string('know_your_car_date'))
            : ($dealer?->know_your_car_date ? $dealer->know_your_car_date->copy() : null);

        $notes = [];
        if ($selectedKycnDate) {
            $notes[] = 'KYCN Date: '.$selectedKycnDate->format('M jS, Y');
        }
        if (!empty($data['vehicle_purchased'])) {
            $notes[] = 'Vehicle Purchased: '.date('M jS, Y', strtotime($data['vehicle_purchased']));
        }
        $notesText = implode("\n", $notes);
        $fullNameRaw = trim($data['first_name'].' '.$data['last_name']);

        $fpData = [
            'dealer_id' => $dealer?->id,
            'know_your_car_date' => $selectedKycnDate?->toDateString(),
            'full_name' => Submission::normalizeName($fullNameRaw),
            'email' => Submission::normalizeEmail($data['email']),
            'phone' => Submission::normalizePhone($data['phone']),
            'guest_count' => (int) $data['number_of_attendees'],
            'wants_appointment' => 0,
            'notes' => Submission::normalizeNotes($notesText),
        ];
        $fingerprint = Submission::makeFingerprint($fpData);

        $duplicateExists = Submission::query()
            ->where('fingerprint', $fingerprint)
            ->exists();

        if ($duplicateExists) {
            return redirect()
                ->route('public.form', $qs)
                ->with('info', 'We have already received your registration')
                ->withInput();
        }

        try {
            $submission = Submission::create([
                'dealer_id' => $dealer?->id,
                'event_id' => null,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'full_name' => $fullNameRaw,
                'email' => $data['email'],
                'phone' => $data['phone'],
                'guest_count' => (int) $data['number_of_attendees'],
                'wants_appointment' => 0,
                'know_your_car_date' => $selectedKycnDate?->toDateString(),
                'vehicle_purchased' => $data['vehicle_purchased'] ?? null,
                'notes' => $notesText,
                'meta_json' => json_encode($request->all(), JSON_UNESCAPED_SLASHES),
                'fingerprint' => $fingerprint,
            ]);
        } catch (\Throwable $e) {
            if ((string) $e->getCode() == '23000') {
                return redirect()
                    ->route('public.form', $qs)
                    ->with('info', 'We have already received your registration')
                    ->withInput();
            }

            SubmissionNotifier::notifyFailure([
                'source' => 'public.form.store',
                'input' => $request->except(['_token']),
                'dealer_id' => $dealer?->id,
            ], $e);

            throw $e;
        }

        try {
            SubmissionNotifier::notifySuccess($submission);
        } catch (\Throwable $e) {
            SubmissionNotifier::notifyFailure([
                'source' => 'public.form.store',
                'submission_id' => $submission->id,
                'dealer_id' => $submission->dealer_id,
            ], $e);
        }

        return redirect()
            ->route('public.form', $qs)
            ->with('success', 'Your Registration has Been Received');
    }
}
