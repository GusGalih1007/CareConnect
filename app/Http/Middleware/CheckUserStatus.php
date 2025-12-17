<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user)
        {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum login. Diharapkan login terlebih dahulu.'
                ], 403);
            }

            return redirect()->route('login.form')
                ->with('error', 'Anda belum login. Diharapkan login terlebih dahulu.');
        }

        if ($user && $user->is_active == false)
        {
            Auth::logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda dinonaktifkan. Silakan hubungi admin.'
                ], 403);
            }

            return redirect()->route('login.form')
                ->with('error', 'Akun Anda dinonaktifkan. Silakan hubungi admin.');
        }
        
        return $next($request);
    }
}
