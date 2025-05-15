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
        $subadmin = Auth::guard('subadmin')->user();
        $admin = Auth::guard('admin')->user();

        if($admin){
            return $next($request);
        }

        if (!$subadmin) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

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
