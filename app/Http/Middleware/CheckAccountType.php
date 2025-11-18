<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$types): Response
    {
        if (!(auth()->check())) {
            // User is not authenticated, redirect to login
            return redirect('/');
        }

        if (!in_array(auth()->user()->type, $types)) {
            // User type does not match, redirect or show error
            return redirect('/')->with('error', 'Unauthorized access.');
        }
        return $next($request);
    }
}
