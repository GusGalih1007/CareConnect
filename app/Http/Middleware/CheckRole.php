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
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Cek role name dari user
        $userRole = $user->role->role_name;
        
        if (!in_array($userRole, $roles)) {
            abort(403, 'Unauthorized action.');
        }
        
        return $next($request);
    }
}