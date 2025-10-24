<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Random\RandomException;

class DealerController extends Controller
{
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
     * @return View
     */
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $dealers = Dealer::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", [$q !== '' ? "{$q}%" : '%'])
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        return view('admin.dealers.index', compact('dealers', 'q'));
    }

    /**
     * @param Dealer $dealer
     *
     * @return View
     */
    public function edit(Dealer $dealer): View
    {
        return view('admin.dealers.edit', compact('dealer'));
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
            'name' => ['required', 'string', 'max:255'],
            'dealership_logo' => ['nullable', 'url'],
            'logo_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
        ]);

        $logoPath = null;
        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('logos', 'public');
            $logoPath = Storage::url($path);
        } elseif (!empty($data['dealership_logo'])) {
            $logoPath = $data['dealership_logo'];
        }

        $code = Str::slug($data['name']);
        $suffix = '';
        while (Dealer::where('code', $code.$suffix)->exists()) {
            $suffix = '-'.substr(bin2hex(random_bytes(2)),0,3);
        }

        Dealer::create([
            'name' => $data['name'],
            'code' => $code.$suffix,
            'dealership_logo' => $logoPath,
        ]);

        return redirect()->route('admin.dealers.index')->with('success', 'Dealer created.');
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
            'name' => ['required', 'string', 'max:255'],
            'dealership_logo' => ['nullable', 'url'],
            'logo_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $logo = $dealer->dealership_logo;

        if ($request->boolean('remove_logo')) {
            $logo = null;
        }

        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('logos', 'public');
            $logo = Storage::url($path);
        } elseif (!empty($data['dealership_logo'])) {
            $logo = $data['dealership_logo'];
        }

        $dealer->update([
            'name' => $data['name'],
            'dealership_logo' => $logo,
        ]);

        return redirect()->route('admin.dealers.index')->with('success', 'Dealer updated.');
    }
}
