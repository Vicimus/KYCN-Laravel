<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Random\RandomException;

class PublicFormController extends Controller
{
    /**
     * @param Request $request
     *
     * @return View
     */
    public function show(Request $request): View
    {
        $code = (string) $request->query('d', '');
        $dealer = $code !== '' ? Dealer::where('code', $code)->first() : null;

        $logo = $dealer?->dealership_logo ?: 'https://vicimus.com/wp-content/uploads/2023/08/bumper.svg';

        return view('public.form', [
            'dealer' => $dealer,
            'logo' => $logo,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     *
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
            'know_your_car_date' => 'nullable|date',
            'vehicle_purchased' => 'nullable|date',
        ]);

        $dealer = null;
        if ($request->query('d')) {
            $dealer = Dealer::where('code', $request->query('d'))->first();
        }
        if (!$dealer && !empty($data['dealership_name'])) {
            $dealer = Dealer::firstOrCreate(
                ['name' => trim($data['dealership_name'])],
                ['code' => Str::upper(preg_replace('/[^A-Za-z0-9]+/', '', $data['dealership_name'])) ?: 'DEALER' . random_int(1000, 9999)]
            );
        }
        if (!$dealer) {
            $dealer = Dealer::orderBy('id')->first();
        }

        $notes = [];
        if (!empty($data['know_your_car_date'])) {
            $notes[] = 'KYCN Date: ' . date('M jS, Y', strtotime($data['know_your_car_date']));
        }
        if (!empty($data['vehicle_purchased'])) {
            $notes[] = 'Vehicle Purchased: ' . date('M jS, Y', strtotime($data['vehicle_purchased']));
        }
        $notesText = implode("\n", $notes);

        Submission::create([
            'dealer_id' => $dealer?->id,
            'event_id' => null,
            'full_name' => trim($data['first_name'] . ' ' . $data['last_name']),
            'email' => $data['email'],
            'phone' => $data['phone'],
            'vehicle_year' => null,
            'vehicle_make' => null,
            'vehicle_model' => null,
            'guest_count' => (int) $data['number_of_attendees'],
            'wants_appointment' => 0,
            'notes' => $notesText,
            'meta_json' => json_encode($request->all(), JSON_UNESCAPED_SLASHES),
        ]);

        return redirect()->route('public.thankyou');
    }
}
