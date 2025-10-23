<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Models\Submission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * @param Request $request
     *
     * @return View
     */
    public function index(Request $request): View
    {
        $start = $request->query('start', now()->subDays(90)->toDateString());
        $end = $request->query('end', now()->addDays(90)->toDateString());
        $dealerCode = (string) $request->query('dealer', '');

        $dealer = $dealerCode ? Dealer::where('code', $dealerCode)->first() : null;

        $q = Submission::query()
            ->with('dealer')
            ->whereBetween('created_at', [Carbon::parse($start)->startOfDay(), Carbon::parse($end)->endOfDay()])
            ->when($dealer, fn($qq) => $qq->where('dealer_id', $dealer->id))
            ->latest()
            ->limit(500);

        $rows = $q->get();

        $byDealer = Dealer::query()
            ->select('dealers.*')
            ->selectSub(function ($sq) use ($start, $end) {
                $sq->from('submissions as s')
                    ->selectRaw('count(1)')
                    ->whereColumn('s.dealer_id', 'dealers.id')
                    ->whereBetween('s.created_at', [Carbon::parse($start)->startOfDay(), Carbon::parse($end)->endOfDay()]);
            }, 'cnt')
            ->orderByDesc('cnt')
            ->orderBy('name')
            ->get();

        return view('admin.dashboard', [
            'rows' => $rows,
            'byDealer' => $byDealer,
            'start' => $start,
            'end' => $end,
            'dealerCode' => $dealerCode,
            'dealerForEmbed' => $dealerCode ?: '',
        ]);
    }
}
