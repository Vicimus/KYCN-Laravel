<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * @return View
     */
    public function show(): View
    {
        return view('auth.login');
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate(['password' => 'required']);

        $ok = hash_equals(
            (string) config('app.admin_password', env('ADMIN_PASSWORD', '')),
            (string) $request->password
        );

        if (!$ok) {
            return back()->withErrors(['password' => 'Incorrect password.'])->withInput();
        }

        session(['is_admin' => true]);

        return redirect()->route('admin.dealers.index');
    }

    /**
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        session()->flush();

        return redirect()->route('admin.login.show');
    }
}
