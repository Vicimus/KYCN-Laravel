<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Models\Submission;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * @return StreamedResponse
     */
    public function allCsv(): StreamedResponse
    {
        return $this->streamCsv('kycn_all_' . now()->format('Ymd_His') . '.csv', function ($push) {
            $q = Submission::with('dealer')->orderByDesc('created_at');
            $this->applyFilters($q);

            $q->chunk(100, function ($chunk) use ($push) {
                foreach ($chunk as $s) {
                    $push([
                        optional($s->dealer)->name,
                        $s->full_name,
                        $s->email,
                        $s->phone,
                        (int)$s->guest_count,
                        $s->wants_appointment ? 'Yes' : 'No',
                        optional($s->know_your_car_date)?->format('Y-m-d'),
                        optional($s->vehicle_purchased)?->format('Y-m-d'),
                        $s->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                    ]);
                }
            });
        });
    }

    /**
     * @return StreamedResponse
     */
    public function allIcs(): StreamedResponse
    {
        return $this->streamIcs('kycn_all_' . now()->format('Ymd_His') . '.ics', function () {
            $events = [];
            $q = Submission::with('dealer')->orderByDesc('created_at');
            $this->applyFilters($q);

            $q->chunk(200, function ($chunk) use (&$events) {
                foreach ($chunk as $s) {
                    if (!$s->know_your_car_date) {
                        continue;
                    }
                    $date = CarbonImmutable::parse($s->know_your_car_date);
                    $events[] = [
                        'uid' => 'kycn-' . $s->id . '@' . request()->getHost(),
                        'summary' => 'Know Your Car Night — ' . optional($s->dealer)->name,
                        'dtstart' => $date->format('Ymd'),
                        'dtend' => $date->addDay()->format('Ymd'),
                        'description' => $s->full_name . ' • ' . ($s->email ?: '') . ' • ' . ($s->phone ?: ''),
                        'location' => optional($s->dealer)->name,
                    ];
                }
            });

            return $events;
        });
    }

    /**
     * @param Dealer $dealer
     *
     * @return StreamedResponse
     */
    public function dealerCsv(Dealer $dealer): StreamedResponse
    {
        return $this->streamCsv(
            'kycn_' . $dealer->code . '_' . now()->format('Ymd_His') . '.csv',
            function ($push) use ($dealer) {
                $q = Submission::where('dealer_id', $dealer->id)->orderByDesc('created_at');
                $this->applyFilters($q);

                $q->chunk(100, function ($chunk) use ($dealer, $push) {
                    foreach ($chunk as $s) {
                        $push([
                            $dealer->name,
                            $s->full_name,
                            $s->email,
                            $s->phone,
                            (int)$s->guest_count,
                            $s->wants_appointment ? 'Yes' : 'No',
                            optional($s->know_your_car_date)?->format('Y-m-d'),
                            optional($s->vehicle_purchased)?->format('Y-m-d'),
                            $s->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                        ]);
                    }
                });
            }
        );
    }

    /**
     * @param Dealer $dealer
     *
     * @return StreamedResponse
     */
    public function dealerIcs(Dealer $dealer): StreamedResponse
    {
        return $this->streamIcs('kycn_' . $dealer->code . '_' . now()->format('Ymd_His') . '.ics', function () use ($dealer) {
            $events = [];
            $q = Submission::where('dealer_id', $dealer->id)->orderByDesc('created_at');
            $this->applyFilters($q);

            $q->chunk(200, function ($chunk) use (&$events, $dealer) {
                foreach ($chunk as $s) {
                    if (!$s->know_your_car_date) {
                        continue;
                    }
                    $date = CarbonImmutable::parse($s->know_your_car_date);
                    $events[] = [
                        'uid' => 'kycn-' . $s->id . '@' . request()->getHost(),
                        'summary' => 'Know Your Car Night — ' . $dealer->name,
                        'dtstart' => $date->format('Ymd'),
                        'dtend' => $date->addDay()->format('Ymd'),
                        'description' => $s->full_name . ' • ' . ($s->email ?: '') . ' • ' . ($s->phone ?: ''),
                        'location' => $dealer->name,
                    ];
                }
            });

            return $events;
        });
    }

    /**
     * @param Builder $q
     *
     * @return Builder
     */
    protected function applyFilters(Builder $q): Builder
    {
        if ($from = request('from')) {
            $q->whereDate('created_at', '>=', $from);
        }

        if ($to = request('to')) {
            $q->whereDate('created_at', '<=', $to);
        }

        if ($term = trim((string)request('q'))) {
            $q->where(function ($qq) use ($term) {
                $qq->where('full_name', 'like', "%$term%")
                    ->orWhere('email', 'like', "%$term%")
                    ->orWhere('phone', 'like', "%$term%")
                    ->orWhereHas('dealer', fn($dq) => $dq->where('name', 'like', "%$term%"));
            });
        }

        return $q;
    }

    /**
     * @param string $s
     *
     * @return string
     */
    protected static function icsEscape(string $s): string
    {
        return str_replace(
            ["\\", ";", ",", "\n", "\r"],
            ["\\\\", "\;", "\,", "\\n", ""],
            $s,
        );
    }

    /**
     * @param string  $filename
     * @param Closure $writer
     *
     * @return StreamedResponse
     */
    protected function streamCsv(string $filename, Closure $writer): StreamedResponse
    {
        return response()->streamDownload(function () use ($writer) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Dealer',
                'Full Name',
                'Email',
                'Phone',
                'Guests',
                'Wants Appointment',
                'KYCN Date',
                'Vehicle Purchased',
                'Created At',
            ]);

            $writer(function (array $row) use ($out) {
                fputcsv($out, $row);
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param string  $filename
     * @param Closure $yieldEvents
     *
     * @return StreamedResponse
     */
    protected function streamIcs(string $filename, Closure $yieldEvents): StreamedResponse
    {
        return response()->streamDownload(function () use ($yieldEvents) {
            $out = fopen('php://output', 'w');
            $write = fn($l) => fwrite($out, $l . "\r\n");

            $write('BEGIN:VCALENDAR');
            $write('VERSION:2.0');
            $write('PRODID:-//KYCN//Bumper//EN');
            $write("CALSCALE:GREGORIAN");
            $write("METHOD:PUBLISH");

            foreach ($yieldEvents() as $ev) {
                $write('BEGIN:VEVENT');
                $write('UID:' . $ev['uid']);
                $write('DTSTAMP:' . now()->setTimezone('UTC')->format('Ymd\THis\Z'));
                $write('SUMMARY:' . self::icsEscape($ev['summary']));
                $write('DTSTART;VALUE=DATE:' . $ev['dtstart']);

                if (!empty($ev['dtend'])) {
                    $write('DTEND;VALUE=DATE:' . $ev['dtend']);
                }

                if (!empty($ev['description'])) {
                    $write('DESCRIPTION:' . self::icsEscape($ev['description']));
                }

                if (!empty($ev['location'])) {
                    $write('LOCATION:' . self::icsEscape($ev['location']));
                }

                $write('END:VEVENT');
            }

            $write('END:VCALENDAR');
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
        ]);
    }
}
