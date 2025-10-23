<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Random\RandomException;

class DealerController extends Controller
{
    /**
     * @return View
     */
    public function index(): View
    {
        $dealers = Dealer::orderBy('name')->get();

        return view('admin.dealers.index', compact('dealers'));
    }

    /**
     * @return View
     */
    public function create(): View
    {
        return view('admin.dealers.create');
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
            'name' => 'required|string|max:255',
            'dealership_logo' => 'nullable|url|max:2048',
        ]);

        $code = Str::upper(preg_replace('/[^A-Za-z0-9]+/', '', $data['name'])) ?: 'DEALER' . random_int(1000, 9999);

        Dealer::create([
            'name' => $data['name'],
            'code' => $code,
            'dealership_logo' => $data['dealership_logo'] ?? null,
        ]);

        return redirect()->route('admin.dealers.index')->with('success', 'Dealer created.');
    }

    /**
     * @param Dealer $dealer
     *
     * @return View
     */
    public function show(Dealer $dealer): View
    {
        $rows = Submission::where('dealer_id', $dealer->id)
            ->orderByDesc('created_at')
            ->get();

        return view('admin.dealers.show', compact('dealer', 'rows'));
    }
}
