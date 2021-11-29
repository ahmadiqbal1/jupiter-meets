<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GlobalConfig;
use Illuminate\Support\Facades\Cache;
use App\Models\Currency;

class GlobalConfigController extends Controller
{
    /**
     * Manage site settings.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = GlobalConfig::where('key', '<>', 'VERSION')->get();

        return view('admin.global-config.index', [
            'page' => 'Global Configuration',
            'data' => $data,
        ]);
    }

    //return the edit page
    public function edit($id)
    {
        $model = GlobalConfig::find($id);

        return view('admin.global-config.edit', [
            'page' => 'Global Configuration',
            'model' => $model,
            'currencies' => $model->key == 'CURRENCY' ? Currency::get() : '',
        ]);
    }

    //update global configuration
    public function update(Request $request)
    {
        $file = $request->image;
        $model = GlobalConfig::find($request->id);

        if ($file && $file->isValid()) {
            $request->validate([
                'image' => 'required|mimes:png|max:1024',
            ]);

            $model->value = $model->key . '.png';
            $file->storeAs('public/images', $model->key . '.png');
        } else {
            if ($request->key == "PAYMENT_MODE" && $request->value == "enabled") {
                $license_notifications_array=aplVerifyLicense('', true);

                if ($license_notifications_array['notification_case'] != "notification_license_ok") {
                    return json_encode(['success' => false, 'error' => $license_notifications_array['notification_text']]);
                } else if ($license_notifications_array['notification_data'] != "Extended License") {
                    return json_encode(['success' => false, 'error' => 'You will need an Extended License to activate the payment module.']);
                }
            }

            $request->validate([
                'value' => 'required|max:255',
            ]);
            $model->value = $request->value;
        }

        if ($model->save()) {
            Cache::forget('settings');
            Cache::forget('symbol');
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false, 'error' => 'An error occurred, please try again.']);
    }
}
