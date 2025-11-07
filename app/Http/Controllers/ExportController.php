<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Models\Submission;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function allCsv(): StreamedResponse
    {
        $filename = 'kycn-all-'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'created_at', 'dealer', 'event_id', 'full_name', 'email', 'phone', 'guest_count', 'wants_appointment', 'know_your_car_date', 'vehicle_purchased', 'notes']);

            Submission::with('dealer')
                ->orderBy('created_at', 'asc')
                ->chunk(500, function ($chunk) use ($out) {
                    foreach ($chunk as $r) {
                        fputcsv($out, [
                            $r->id,
                            optional($r->created_at)->format('Y-m-d H:i:s'),
                            optional($r->dealer)->name,
                            $r->event_id,
                            $r->full_name,
                            $r->email,
                            $r->phone,
                            (int) $r->guest_count,
                            $r->wants_appointment ? 1 : 0,
                            optional($r->know_your_car_date)->format('Y-m-d'),
                            optional($r->vehicle_purchased)->format('Y-m-d'),
                            $r->notes,
                        ]);
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    // ====== ADMIN: all dealers ICS ======
    public function allIcs()
    {
        $subs = Submission::query()
            ->whereNotNull('know_your_car_date')
            ->with('dealer')
            ->orderBy('know_your_car_date', 'asc')
            ->get();

        return $this->icsResponse('KYCN-All.ics', $subs);
    }

    // ====== ADMIN: per-dealer CSV ======
    public function dealerCsv(Dealer $dealer): StreamedResponse
    {
        $filename = 'kycn-'.$dealer->code.'-'.now()->format('Ymd_His').'.csv';
        $subs = $dealer->submissions()->orderBy('created_at', 'asc')->get();

        return response()->streamDownload(function () use ($subs, $dealer) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'created_at', 'dealer', 'event_id', 'full_name', 'email', 'phone', 'guest_count', 'wants_appointment', 'know_your_car_date', 'vehicle_purchased', 'notes']);
            foreach ($subs as $r) {
                fputcsv($out, [
                    $r->id,
                    optional($r->created_at)->format('Y-m-d H:i:s'),
                    $dealer->name,
                    $r->event_id,
                    $r->full_name,
                    $r->email,
                    $r->phone,
                    (int) $r->guest_count,
                    $r->wants_appointment ? 1 : 0,
                    optional($r->know_your_car_date)->format('Y-m-d'),
                    optional($r->vehicle_purchased)->format('Y-m-d'),
                    $r->notes,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    // ====== ADMIN: per-dealer ICS ======
    public function dealerIcs(Dealer $dealer)
    {
        $subs = $dealer->submissions()
            ->whereNotNull('know_your_car_date')
            ->orderBy('know_your_car_date', 'asc')
            ->get();

        $fname = 'KYCN-'.$dealer->code.'.ics';

        return $this->icsResponse($fname, $subs, $dealer);
    }

    // ====== PUBLIC: token CSV ======
    public function publicDealerCsv(string $token): StreamedResponse
    {
        $dealer = Dealer::where('portal_token', $token)->firstOrFail();
        $filename = 'kycn-'.$dealer->code.'-'.now()->format('Ymd_His').'.csv';
        $subs = $dealer->submissions()->orderBy('created_at', 'asc')->get();

        return response()->streamDownload(function () use ($subs, $dealer) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'created_at', 'dealer', 'event_id', 'full_name', 'email', 'phone', 'guest_count', 'wants_appointment', 'know_your_car_date', 'vehicle_purchased', 'notes']);
            foreach ($subs as $r) {
                fputcsv($out, [
                    $r->id,
                    optional($r->created_at)->format('Y-m-d H:i:s'),
                    $dealer->name,
                    $r->event_id,
                    $r->full_name,
                    $r->email,
                    $r->phone,
                    (int) $r->guest_count,
                    $r->wants_appointment ? 1 : 0,
                    optional($r->know_your_car_date)->format('Y-m-d'),
                    optional($r->vehicle_purchased)->format('Y-m-d'),
                    $r->notes,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    // ====== PUBLIC: token ICS ======
    public function publicDealerIcs(string $token)
    {
        $dealer = Dealer::where('portal_token', $token)->firstOrFail();
        $subs = $dealer->submissions()
            ->whereNotNull('know_your_car_date')
            ->orderBy('know_your_car_date', 'asc')
            ->get();

        $fname = 'KYCN-'.$dealer->code.'.ics';

        return $this->icsResponse($fname, $subs, $dealer);
    }

    // ================== Helpers ==================

    /**
     * Build an ICS response from submissions that have know_your_car_date.
     * Event time window: 6:00 PM–7:00 PM local server time (customize as needed).
     */
    protected function icsResponse(string $filename, $submissions, ?Dealer $dealer = null)
    {
        $lines = [];
        $lines[] = 'BEGIN:VCALENDAR';
        $lines[] = 'VERSION:2.0';
        $lines[] = 'PRODID:-//KYCN//Dashboard//EN';
        $lines[] = 'CALSCALE:GREGORIAN';
        $lines[] = 'METHOD:PUBLISH';

        foreach ($submissions as $s) {
            if (! $s->know_your_car_date) {
                continue;
            }

            $date = Carbon::parse($s->know_your_car_date); // date only
            $start = $date->copy()->setTime(18, 0, 0); // 6:00 PM
            $end = $date->copy()->setTime(19, 0, 0); // 7:00 PM

            $uid = 'kycn-'.$s->id.'@'.parse_url(config('app.url'), PHP_URL_HOST);
            $summary = ($dealer?->name ?: optional($s->dealer)->name ?: 'Know Your Car Night').' — '.$s->full_name;

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:'.$uid;
            $lines[] = 'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z');
            $lines[] = 'DTSTART:'.$start->format('Ymd\THis');
            $lines[] = 'DTEND:'.$end->format('Ymd\THis');
            $lines[] = 'SUMMARY:'.$this->icsEscape($summary);
            if ($s->notes) {
                $lines[] = 'DESCRIPTION:'.$this->icsEscape($s->notes);
            }
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';
        $body = implode("\r\n", $lines)."\r\n";

        return response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    protected function icsEscape(string $text): string
    {
        // Basic ICS escaping
        $text = str_replace(['\\', ';', ',', "\n"], ['\\\\', "\;", "\,", '\\n'], $text);

        return $text;
    }
}
