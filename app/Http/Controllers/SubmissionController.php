<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Models\Submission;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Random\RandomException;

class SubmissionController extends Controller
{
    /**
     * @param Request $request
     *
     * @return View
     */
    public function create(Request $request): View
    {
        $d = (string) $request->query('d', '');
        $embed = $request->boolean('embed');

        $dealer = $d ? Dealer::where('code', $d)->orWhere('dealership_url', $d)->first() : null;

        return view('submissions.form', [
            'embed' => $embed,
            'dealerCode' => $d,
            'dealer' => $dealer,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     *
     * @throws RandomException
     */
    public function store(Request $request): RedirectResponse|Response
    {
        $embed = $request->boolean('embed');

        $validated = $request->validate([
            'dealership_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'number_of_attendees' => 'required|integer|min:1|max:10',
            'know_your_car_date' => 'nullable|date',
            'vehicle_purchased' => 'nullable|date',
            'd' => 'nullable|string|max:255',
        ]);

        $dealer = null;
        if (!empty($validated['d'])) {
            $dealer = Dealer::where('code', $validated['d'])
                ->orWhere('dealership_url', $validated['d'])
                ->first();
        }

        if (!$dealer) {
            $dealer = Dealer::where('name', $validated['dealership_name'])->first();
            if (!$dealer) {
                $dealer = Dealer::create([
                    'name' => $validated['dealership_name'],
                    'code' => Str::upper(preg_replace('/[^A-Za-z0-9]+/', '', $validated['dealership_name'])) ?: 'DEALER' . random_int(1000, 9999),
                    'portal_token' => bin2hex(random_bytes(16)),
                ]);
            }
        }

        $prettyDate = function (?string $d): ?string {
            if (!$d) return null;
            try {
                return Carbon::parse($d)->format('M jS, Y');
            } catch (\Throwable) {
                return $d;
            }
        };

        $notesParts = [];
        if (!empty($validated['know_your_car_date'])) {
            $notesParts[] = 'KYCN Date: ' . $prettyDate($validated['know_your_car_date']);
        }
        if (!empty($validated['vehicle_purchased'])) {
            $notesParts[] = 'Vehicle Purchased: ' . $prettyDate($validated['vehicle_purchased']);
        }
        $notes = implode("\n", $notesParts);

        $submission = Submission::create([
            'dealer_id' => $dealer->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'full_name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'guest_count' => (int) $validated['number_of_attendees'],
            'wants_appointment' => false,
            'know_your_car_date' => $validated['know_your_car_date'] ?? null,
            'vehicle_purchased' => $validated['vehicle_purchased'] ?? null,
            'notes' => $notes,
            'meta_json' => $request->except(['_token']),
        ]);

        try {
            Mail::raw(
                "New KYCN Registration\n\n" .
                "Dealer: {$dealer->name}\n" .
                "Name:   {$submission->full_name}\n" .
                "Email:  {$submission->email}\n" .
                "Phone:  {$submission->phone}\n" .
                "Guests: {$submission->guest_count}\n" .
                ($submission->know_your_car_date ? "KYCN Date: " . $prettyDate($submission->know_your_car_date) . "\n" : "") .
                ($submission->vehicle_purchased ? "Vehicle Purchased: " . $prettyDate($submission->vehicle_purchased) . "\n" : ""),
                function ($m) {
                    $m->to(['craig@vicimus.com', 'tgray@vicimus.com', 'cmachado@vicimus.com'])
                        ->subject('New KYCN Registration');
                }
            );
        } catch (\Throwable $e) {
            // Non-fatal in dev; log if you want
            // logger()->warning('Mail send failed: '.$e->getMessage());
        }

        if ($embed) {
            return response()->view('submissions.embedded-submitted');
        }

        return redirect()->route('submissions.create')->with('success', 'Your Registration has Been Received');
    }
}
