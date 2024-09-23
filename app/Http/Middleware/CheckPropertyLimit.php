<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPropertyLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Obtener el paquete activo del usuario
        $package = $user->userPackages()
            ->where('remaining_listings', '>', 0)
            ->first();

        // Si el usuario no tiene un paquete activo
        if (!$package) {
            return redirect(route('myProperties'))->with('error', 'No tienes publicaciones disponibles, adquiere un paquete para continuar.');
        }

        return $next($request);
    }
}
