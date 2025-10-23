<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use Carbon\Carbon;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class IcsController extends Controller
{
    /**
     * @param Request $request
     *
     * @return ResponseFactory|Response
     */
    public function feed(Request $request): ResponseFactory|Response
    {
        $dealerCode = (string)$request->query('dealer', '');
        $dealer = Dealer::where('code', $dealerCode)->firstOrFail();

        $events = $dealer->submissions()->orderByDesc('created_at')->limit(500)->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//KYCN//Calendar//EN',
        ];

        foreach ($events as $ev) {
            $start = $ev->know_your_car_date
                ? Carbon::parse($ev->know_your_car_date)->format('Ymd')
                : Carbon::parse($ev->created_at)->format('Ymd');
            $uid = Str::uuid();

            $summary = 'KYCN RSVP: ' . $ev->full_name . ' (' . $dealer->name . ')';
            $desc = "Guests: {$ev->guest_count}\\nEmail: {$ev->email}\\nPhone: {$ev->phone}";
            $lines = array_merge($lines, [
                'BEGIN:VEVENT',
                "UID:{$uid}",
                "DTSTAMP:" . Carbon::parse($ev->created_at)->utc()->format('Ymd\THis\Z'),
                "DTSTART:{$start}",
                "SUMMARY:" . addcslashes($summary, ",;"),
                "DESCRIPTION:" . addcslashes($desc, ",;"),
                'END:VEVENT',
            ]);
        }

        $lines[] = 'END:VCALENDAR';

        return response(implode("\r\n", $lines), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
        ]);
    }
}
