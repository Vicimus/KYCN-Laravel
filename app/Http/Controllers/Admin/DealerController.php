<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\Submission;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Random\RandomException;
use Throwable;

class DealerController extends Controller
{
    public function create(): View
    {
        $dealer = new Dealer;

        return view('admin.dealers.form', [
            'dealer' => $dealer,
            'mode' => 'create',
        ]);
    }

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
            ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [$q !== '' ? "{$q}%" : '%'])
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        return view('admin.dealers.index', compact('dealers', 'q'));
    }

    public function edit(Dealer $dealer): View
    {
        return view('admin.dealers.form', [
            'dealer' => $dealer,
            'mode' => 'edit',
        ]);
    }

    public function show(Request $request, Dealer $dealer): View
    {
        $rowsQuery = Submission::where('dealer_id', $dealer->id)
            ->orderByRaw('know_your_car_date IS NULL')
            ->orderBy('know_your_car_date')
            ->orderBy('last_name')
            ->orderBy('first_name');

        $startDate = null;
        $endDate = null;

        if ($request->filled('start_date')) {
            try {
                $startDate = Carbon::parse($request->input('start_date'))->toDateString();
                $rowsQuery->whereDate('know_your_car_date', '>=', $startDate);
            } catch (Throwable) {
                $startDate = null;
            }
        }

        if ($request->filled('end_date')) {
            try {
                $endDate = Carbon::parse($request->input('end_date'))->toDateString();
                $rowsQuery->whereDate('know_your_car_date', '<=', $endDate);
            } catch (Throwable) {
                $endDate = null;
            }
        }

        $q = trim((string) $request->query('q', ''));
        if ('' !== $q) {
            $like = sprintf('%s%s%s', '%', $q, '%');
            $rowsQuery->where(function ($query) use ($like) {
                $query->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhereRaw("concat_ws(' ', first_name, last_name) like ?", [$like])
                    ->orWhere('email', 'like', $like)
                    ->orWhere('notes', 'like', $like);
            });
        }

        $rows = $rowsQuery->get();

        return view('admin.dealers.show', compact(
            'dealer',
            'rows',
            'startDate',
            'endDate',
            'q',
        ));
    }

    /**
     * @throws RandomException
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dealership_logo' => ['nullable', 'url'],
            'logo_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'know_your_car_date' => ['nullable', 'date'],
        ]);

        $logoPath = null;
        if ($request->hasFile('logo_file')) {
            $logoPath = $this->storeLogo($request->file('logo_file'));
            $logoPath = $this->normalizeLogo($logoPath);
        } elseif (! empty($data['dealership_logo'])) {
            $logoPath = $this->normalizeLogo($data['dealership_logo']);
        }

        $code = Str::slug($data['name']);
        $suffix = '';
        while (Dealer::where('code', $code.$suffix)->exists()) {
            $suffix = '-'.substr(bin2hex(random_bytes(2)), 0, 3);
        }

        Dealer::create([
            'name' => $data['name'],
            'code' => $code.$suffix,
            'dealership_logo' => $logoPath,
            'know_your_car_date' => $data['know_your_car_date'] ?? null,
        ]);

        return redirect()->route('admin.dealers.index')->with('success', 'Dealer created.');
    }

    public function update(Request $request, Dealer $dealer): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dealership_logo' => ['nullable', 'url'],
            'logo_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'know_your_car_date' => ['nullable', 'date'],
        ]);

        $logo = $dealer->dealership_logo;

        if ($request->boolean('remove_logo')) {
            $this->deleteLogo($logo);
            $logo = null;
        }

        if ($request->hasFile('logo_file')) {
            $this->deleteLogo($logo);
            $logo = $this->storeLogo($request->file('logo_file'));
            $logo = $this->normalizeLogo($logo);
        } elseif (! empty($data['dealership_logo'])) {
            $logo = $this->normalizeLogo($data['dealership_logo']);
        }

        $dealer->update([
            'name' => $data['name'],
            'dealership_logo' => $logo,
            'know_your_car_date' => $data['know_your_car_date'] ?? null,
        ]);

        return redirect()->route('admin.dealers.index')->with('success', 'Dealer updated.');
    }

    /**
     * Remove a previously uploaded logo if it lives within the public directory.
     */
    private function deleteLogo(?string $path): void
    {
        if (empty($path) || Str::startsWith($path, ['http://', 'https://', '//'])) {
            return;
        }

        $fullPath = public_path(ltrim($path, '/'));

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    /**
     * @param string|null $value
     *
     * @return string|null
     */
    private function normalizeLogo(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            $value = preg_replace('#^http://#i', 'https://', $value);
            if (Str::startsWith($value, '//')) {
                $value = 'https:' . $value;
            }
            return $value;
        }

        return secure_url(ltrim($value, '/'));
    }

    /**
     * Persist the uploaded logo inside public/images so it is web-accessible.
     */
    private function storeLogo(UploadedFile $file): string
    {
        $directory = 'images/dealers';
        $publicDirectory = public_path($directory);

        if (! File::exists($publicDirectory)) {
            File::makeDirectory($publicDirectory, 0755, true);
        }

        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid().'.'.($extension ?: 'png');

        $file->move($publicDirectory, $filename);

        return '/'.$directory.'/'.$filename;
    }
}
