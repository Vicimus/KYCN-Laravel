<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('is_admin')) {
            // remember where we were going
            return redirect()->route('login.show')->with('error', 'Please sign in.');
        }

        return $next($request);
    }
}
