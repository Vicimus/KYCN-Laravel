<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Models\Submission;
use Carbon\Carbon;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class IcsController extends Controller
{
    /**
     * @return ResponseFactory|Response
     */
    public function feed(Request $request)
    {
        $request->validate([
            'dealer' => 'required|string',
        ]);

        $dealer = Dealer::where('code', $request->dealer)->firstOrFail();

        $subs = Submission::where('dealer_id', $dealer->id)
            ->latest('created_at')
            ->limit(500)
            ->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Vicimus//KYCN//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
        ];

        foreach ($subs as $s) {
            $kycnDate = $this->findKycnDate($s);

            if (! $kycnDate) {
                continue;
            }

            $dtstart = Carbon::parse($kycnDate)->format('Ymd');
            $dtstamp = Carbon::now()->utc()->format('Ymd\THis\Z');

            $uid = Str::uuid()->toString().'@kycn';

            $summary = 'Know Your Car Night - '.$dealer->name;

            $desc = trim(implode("\n", array_filter([
                'Name: '.($s->full_name ?: '-'),
                $s->email ? 'Email: '.$s->email : null,
                $s->phone ? 'Phone: '.$s->phone : null,
                'Guests: '.(int) $s->guest_count,
            ])));

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:'.$uid;
            $lines[] = 'DTSTAMP:'.$dtstamp;
            $lines[] = 'DTSTART;VALUE=DATE:'.$dtstart;
            $lines[] = 'SUMMARY:'.$this->escape($summary);
            if ($desc !== '') {
                $lines[] = 'DESCRIPTION:'.$this->escape($desc);
            }
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        $ics = implode("\r\n", $lines)."\r\n";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
        ]);
    }

    protected function findKycnDate($submission): ?string
    {
        $meta = is_array($submission->meta_json)
            ? $submission->meta_json
            : (json_decode($submission->meta_json ?? '', true) ?: []);

        if (! empty($meta['know_your_car_date']) && strtotime($meta['know_your_car_date'])) {
            return $meta['know_your_car_date'];
        }

        if (! empty($submission->notes) && preg_match('/KYCN Date\s*:\s*([0-9]{4}-[0-9]{2}-[0-9]{2})/i', $submission->notes, $m)) {
            if (strtotime($m[1])) {
                return $m[1];
            }
        }

        return null;
    }

    protected function escape(string $text): string
    {
        return str_replace(
            ['\\', ';', ',', "\n", "\r"],
            ['\\\\', "\;", "\,", '\\n', ''],
            $text
        );
    }
}
