<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DealerController extends Controller
{
    /**
     * @param Dealer $dealer
     *
     * @return View
     */
    public function edit(Dealer $dealer): View
    {
        return view('dealers.edit', compact('dealer'));
    }

    /**
     * @param Request $request
     * @param Dealer  $dealer
     *
     * @return RedirectResponse
     */
    public function update(Request $request, Dealer $dealer): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'dealership_url' => 'nullable|string|max:255',
            'dealership_logo' => 'nullable|url|max:2048',
        ]);

        $dealer->update($data);

        return redirect()->route('admin.index', ['dealer' => $dealer->code])
            ->with('status', 'Dealer updated.');
    }
}
