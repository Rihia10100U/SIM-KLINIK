<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Contoh pemakaian di routes/web.php:
     *   Route::middleware('role:admin,resepsionis')->group(function () { ... });
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $roleUser = $user?->role?->value;

        if (! $user || ! in_array($roleUser, $roles, true)) {
            abort(403, 'Kamu tidak punya hak akses ke halaman ini.');
        }

        return $next($request);
    }
}
