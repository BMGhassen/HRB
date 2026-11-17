<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Client
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Admins have their own area — don't send them to the "pending" page
        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        if ($user->role === 'client' && $user->state === 'Actif') {
            return $next($request);
        }

        // Client not yet activated — show the waiting page
        if ($user->role === 'client') {
            return redirect()->route('client.pending');
        }

        // Any other role (e.g. vendeur) has no access to the client area
        return redirect('/');
    }
}
