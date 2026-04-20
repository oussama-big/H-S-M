<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Vérifier si le rôle de l'utilisateur est dans la liste autorisée
        if (!in_array(Auth::user()->role, $roles)) {
            return redirect()->route('dashboard')->with('error', "Accès refusé !");
        }

        return $next($request);
    }
}