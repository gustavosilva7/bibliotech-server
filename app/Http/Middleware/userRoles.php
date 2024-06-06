<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class userRoles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) // Isto verifica se o usuário está logado
            return response()->json(['error' => 'Você não está logado'], 401);

        $user = Auth::user();
        if ($user->role->name == $role)
            return $next($request);

        return response()->json(['error' => 'Você não está logado'], 401);
    }
}
