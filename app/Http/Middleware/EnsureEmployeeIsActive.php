<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->employee && !$user->employee->is_active) {
            // If request is an AJAX or JSON request, return 403 Forbidden
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status akun kepegawaian Anda saat ini Non-Aktif. Akses ditangguhkan.',
                ], 403);
            }

            // Show deactivated view
            return response()->view('employee.deactivated', [], 403);
        }

        return $next($request);
    }
}
