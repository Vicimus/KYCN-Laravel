<?php

namespace App\Http\Controllers;

use App\Exports\SubmissionsExport;
use App\Models\Dealer;
use App\Models\Submission;
use Carbon\Carbon;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    /**
     * @return ResponseFactory|Response
     */
    public function allIcs(): ResponseFactory|Response
    {
        $subs = Submission::query()
            ->whereNotNull('know_your_car_date')
            ->with('dealer')
            ->orderBy('know_your_car_date', 'asc')
            ->get();

        return $this->icsResponse('KYCN-All.ics', $subs);
    }

    /**
     * @return BinaryFileResponse
     */
    public function allXlsx(): BinaryFileResponse
    {
        $filename = sprintf('%s%s%s', 'kycn-all-', now()->format('Ymd_His'), '.xlsx');
        $subs = Submission::with('dealer:id,name')
            ->orderBy('full_name')
            ->orderBy('know_your_car_date')
            ->get();

        [$rows, $total] = $this->mapRowsAndTotal($subs);

        return Excel::download(new SubmissionsExport($rows, $total), $filename);
    }

    /**
     * @param Dealer $dealer
     *
     * @return ResponseFactory|Response
     */
    public function dealerIcs(Dealer $dealer): ResponseFactory|Response
    {
        $subs = $dealer->submissions()
            ->whereNotNull('know_your_car_date')
            ->orderBy('know_your_car_date', 'asc')
            ->get();

        $fname = 'KYCN-'.$dealer->code.'.ics';

        return $this->icsResponse($fname, $subs, $dealer);
    }

    /**
     * @param Dealer $dealer
     *
     * @return BinaryFileResponse
     */
    public function dealerXlsx(Dealer $dealer): BinaryFileResponse
    {
        $filename = sprintf('%s%s%s%s%s', 'kycn-', $dealer->code, '-', now()->format('Ymd_His'), '.xlsx');
        $subs = $dealer->submissions()
            ->orderBy('full_name')
            ->orderBy('know_your_car_date')
            ->get();

        [$rows, $total] = $this->mapRowsAndTotal($subs, $dealer);

        return Excel::download(new SubmissionsExport($rows, $total), $filename);
    }

    /**
     * @param string $token
     *
     * @return ResponseFactory|Response
     */
    public function publicDealerIcs(string $token): ResponseFactory|Response
    {
        $dealer = Dealer::where('portal_token', $token)->firstOrFail();
        $subs = $dealer->submissions()
            ->whereNotNull('know_your_car_date')
            ->orderBy('know_your_car_date', 'asc')
            ->get();

        $fname = 'KYCN-'.$dealer->code.'.ics';

        return $this->icsResponse($fname, $subs, $dealer);
    }

    public function publicDealerXlsx(string $token): BinaryFileResponse
    {
        $dealer = Dealer::where('portal_token', $token)->firstOrFail();

        $filename = sprintf('%s%s%s%s%s', 'kycn-', $dealer->code, '-', now()->format('Ymd_His'), '.xlsx');
        $subs = $dealer->submissions()
            ->orderBy('full_name')
            ->orderBy('know_your_car_date')
            ->get();

        [$rows, $total] = $this->mapRowsAndTotal($subs, $dealer);

        return Excel::download(new SubmissionsExport($rows, $total), $filename);
    }

    /**
     * @param string      $filename
     * @param Collection  $submissions
     * @param Dealer|null $dealer
     *
     * @return ResponseFactory|Response
     */
    protected function icsResponse(string $filename, Collection $submissions, ?Dealer $dealer = null): ResponseFactory|Response
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

    /**
     * @param string $text
     *
     * @return string
     */
    protected function icsEscape(string $text): string
    {
        return str_replace(['\\', ';', ',', "\n"], ['\\\\', "\;", "\,", '\\n'], $text);
    }

    /**
     * @param Collection  $submissions
     * @param Dealer|null $fixedDealer
     *
     * @return array
     */
    private function mapRowsAndTotal(Collection $submissions, ?Dealer $fixedDealer = null): array
    {
        $rows = $submissions->map(function (Submission $r) use ($fixedDealer) {
            return [
                $r->id,
                optional($r->created_at)->format('Y-m-d H:i:s'),
                $fixedDealer?->name ?? $r->dealer?->name,
                $r->event_id,
                $r->full_name,
                $r->email,
                $r->phone,
                (int) $r->guest_count,
                $r->wants_appointment ? 1 : 0,
                optional($r->know_your_car_date)->format('Y-m-d'),
                optional($r->vehicle_purchased)->format('Y-m-d'),
                $r->notes,
            ];
        });

        $total = (int) $submissions->sum(fn ($r) => (int) $r->guest_count);

        return [$rows, $total];
    }
}
