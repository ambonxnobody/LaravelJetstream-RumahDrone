<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleIsStaff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->role !== 'staff') {
            $request->session()->flash('flash.banner', 'You are not authorized to access this page');
            $request->session()->flash('flash.bannerStyle', 'danger');
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
