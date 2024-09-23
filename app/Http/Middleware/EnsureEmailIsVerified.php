<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica si el usuario está autenticado y si el correo está verificado
        if (Auth::check() && !Auth::user()->hasVerifiedEmail()) {
            // Redirige al usuario a la página de verificación
            return redirect()->route('verification.notice');
        }
        return $next($request);
    }
}
