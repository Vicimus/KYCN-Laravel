<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Models\Submission;
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
        $request->validate([
            'dealer' => 'required|string',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);

        $dealer = Dealer::where('code', $request->dealer)->firstOrFail();

//        $dealerCode = (string) $request->query('dealer', '');
        $start = Carbon::parse($request->input('start', '2000-01-01'))->startOfDay();
        $end = Carbon::parse($request->input('end', now()))->endOfDay();

        $rows = Submission::where('dealer_id', $dealer->id)
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->get();

        $filename = sprintf('kycn-%s-%s_to_%s.csv',
            $dealer->code, $start->toDateString(), $end->toDateString()
        );

        return response()->streamDownload(function () use ($rows, $dealer) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'id', 'created_at', 'dealer', 'event_id', 'full_name', 'email', 'phone',
                'vehicle_year', 'vehicle_make', 'vehicle_model', 'guest_count', 'wants_appointment', 'notes'
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    Carbon::parse($r->created_at)->format('M jS, Y g:ia'), // <<< formatted
                    $dealer->name,
                    $r->event_id,
                    $r->full_name,
                    $r->email,
                    $r->phone,
                    $r->vehicle_year,
                    $r->vehicle_make,
                    $r->vehicle_model,
                    (int) $r->guest_count,
                    $r->wants_appointment ? 1 : 0,
                    $r->notes,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
