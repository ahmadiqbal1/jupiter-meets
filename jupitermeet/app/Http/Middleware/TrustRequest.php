<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use App\Models\GlobalConfig;
use Illuminate\Support\Facades\Cache;

class TrustRequest
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
        if (!Str::contains($request->path(), ['install'])) {
            $license_notifications_array = aplVerifyLicense();

            if ($license_notifications_array['notification_case'] != "notification_license_ok") {
                abort(403);
            } else if (isset($license_notifications_array['notification_data']) && $license_notifications_array['notification_data'] != "Extended License" && getSetting('PAYMENT_MODE') == 'enabled') {
                $model = GlobalConfig::where('key', 'PAYMENT_MODE')->first();
                $model->value = 'disabled';
                $model->save();
                Cache::forget('settings');
            }
        }

        return $next($request);
    }
}
