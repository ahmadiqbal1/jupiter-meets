<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPlan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //prevent registered users from accessing meetings
        if (Auth::user() && getSetting('PAYMENT_MODE') == 'enabled' && Auth::user()->plan_status == 'expired') {
            return redirect(route('profile'));
        }
        return $next($request);
    }
}
