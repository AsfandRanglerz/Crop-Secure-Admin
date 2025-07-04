<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSubAdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $sideMenuName, $permissionType)
    {
        $admin = Auth::guard('admin')->user();
        $subadmin = Auth::guard('subadmin')->user();

        // Allow admin through
        if ($admin) {
            return $next($request);
        }

        // Check subadmin authentication
        if (!$subadmin) {
            return redirect()->route('login')->withErrors([
                'message' => 'You must be logged in.',
            ]);
        }

        // Check if subadmin is deactivated
        if ($subadmin->status == 0) {
            Auth::guard('subadmin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'message' => 'Your account has been deactivated.',
            ]);
        }

        // Permission check
        $hasPermission = $subadmin->permissions()
            ->whereHas('side_menu', function ($query) use ($sideMenuName) {
                $query->where('name', $sideMenuName);
            })
            ->where('permissions', $permissionType)
            ->exists();

        if (!$hasPermission) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
