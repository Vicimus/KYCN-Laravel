<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminSessionFreshness
{
    private int $idleLimit = 120;

    public function handle(Request $request, Closure $next)
    {
        if (! session('is_admin')) {
            return redirect()->route('admin.login.show')->with('error', 'Please sign in.');
        }

        $last = (int) session('admin_last_activity', 0);
        $now = time();

        if ($last && ($now - $last) > ($this->idleLimit * 60)) {
            session()->forget(['is_admin', 'admin_last_activity']);

            return redirect()->route('admin.login.show')->with('error', 'Session has expired.');
        }

        session(['admin_last_activity' => $now]);

        return $next($request);
    }
}
