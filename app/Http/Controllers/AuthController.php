<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate(['password' => 'required']);

        $ok = hash_equals(
            (string) config('app.admin_password', env('ADMIN_PASSWORD', '')),
            (string) $request->password
        );

        if (! $ok) {
            return back()->withInput()->withErrors(['password' => 'Incorrect password.']);
        }

        session(['is_admin' => true, 'admin_last_activity' => time()]);

        return redirect()->intended(route('admin.dealers.index'));
    }

    public function logout(): RedirectResponse
    {
        session()->flush();

        return redirect()->route('admin.login.show');
    }
}
