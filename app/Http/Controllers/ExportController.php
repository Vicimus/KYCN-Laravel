<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * @param Request $request
     *
     * @return StreamedResponse
     */
    public function csv(Request $request): StreamedResponse
    {
        $dealerCode = (string) $request->query('dealer', '');
        $start = $request->query('start', '2000-01-01');
        $end = $request->query('end', now()->toDateString());

        $dealer = Dealer::where('code', $dealerCode)->firstOrFail();

        $filename = "kycn-{$dealer->code}-{$start}_to_{$end}.csv";

        return new StreamedResponse(function () use ($dealer, $start, $end) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'created_at', 'dealer', 'event_id', 'full_name', 'email', 'phone', 'guest_count', 'wants_appointment', 'notes']);

            $rows = $dealer->submissions()
                ->whereBetween('created_at', [Carbon::parse($start)->startOfDay(), Carbon::parse($end)->endOfDay()])
                ->orderBy('created_at')
                ->cursor();

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    Carbon::parse($r->created_at)->format('Y-m-d H:i:s'),
                    $dealer->name,
                    $r->event_id,
                    $r->full_name,
                    $r->email,
                    $r->phone,
                    (int)$r->guest_count,
                    $r->wants_appointment ? 1 : 0,
                    preg_replace("/\r?\n/", ' | ', (string) $r->notes),
                ]);
            }
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
