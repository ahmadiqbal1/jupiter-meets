<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\UserPlan;
use Illuminate\Support\Facades\Artisan;
use App\Models\GlobalConfig;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = [];

        $users = User::where('role', '<>', 'admin')->get();

        $freeUsers = $users->filter(function ($user) {
            return $user->plan_type == 'free';
        });

        $paidUsers = $users->filter(function ($user) {
            return $user->plan_type == 'paid';
        });

        $data['meeting'] = Meeting::count();
        $data['user'] = $users->count();
        $data['income'] = UserPlan::sum('amount');
        $data['freeUsers'] = count($freeUsers);
        $data['paidUsers'] = count($paidUsers);

        $incomeGraph = UserPlan::select(DB::raw("SUM(amount) as income"), DB::raw("MONTH(created_at) as month"))
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('income', 'month')
            ->toArray();

        $userGraph = User::select(DB::raw("count(*) as count"), DB::raw("MONTH(created_at) as month"))
            ->where('role', 'end-user')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $meetingGraph = Meeting::select(DB::raw("count(*) as count"), DB::raw("MONTH(created_at) as month"))
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $data['montlyIncome'] = json_encode($incomeGraph);
        $data['userGraph'] = json_encode($userGraph);
        $data['meetingGraph'] = json_encode($meetingGraph);

        return view('admin.dashboard', [
            'page' => 'Dashboard',
            'data' => $data,
        ]);
    }

    /**
     * Manage update.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function update()
    {
        return view('admin.update', [
            'page' => 'Manage Update',
        ]);
    }

    //check if an update is available or not
    public function checkForUpdate()
    {
        $license_notifications_array = aplVerifyLicense('', true);

        if ($license_notifications_array['notification_case'] != "notification_license_ok") {
            return json_encode(['success' => false, 'error' => $license_notifications_array['notification_text']]);
        }

        $version_notifications_array = ausGetVersion();

        if ($version_notifications_array['notification_case'] == "notification_operation_ok") {
            $newVersion = $version_notifications_array['notification_data']['version_number'];

            if (getSetting('VERSION') < $newVersion) {
                return json_encode(['success' => true, 'version' => $newVersion, 'changelog' => $version_notifications_array['notification_data']['version_changelog']]);
            } else {
                return json_encode(['success' => false, 'version' => getSetting('VERSION')]);
            }
        } else {
            return json_encode(['success' => false, 'error' => $version_notifications_array['notification_text']]);
        }
    }

    //check if an update is available or not
    public function downloadUpdate()
    {
        $license_notifications_array = aplVerifyLicense('', true);

        if ($license_notifications_array['notification_case'] != "notification_license_ok") {
            return json_encode(['success' => false, 'error' => $license_notifications_array['notification_text']]);
        }

        $download_notifications_array = ausDownloadFile();

        if ($download_notifications_array['notification_case'] == "notification_operation_ok") {
            $model = GlobalConfig::where('key', 'VERSION')->first();
            $model->value = $download_notifications_array['notification_data']['version_number'];
            $model->save();
            
            Cache::forget('settings');
            Cache::forget('content');
            Cache::forget('symbol');

            $query_notifications_array = ausFetchQuery();

            if ($query_notifications_array['notification_case'] == "notification_operation_ok" && $query_notifications_array['notification_data']) {
                DB::statement($query_notifications_array['notification_data']);
            }

            Artisan::call('migrate', ['--force' => true]);
            return json_encode(['success' => true]);
        } else {
            return json_encode(['success' => false, 'error' => $download_notifications_array['notification_text']]);
        }
    }

    /**
     * Manage license.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function license()
    {
        return view('admin.license', [
            'page' => 'Manage License',
        ]);
    }

    //verify license
    public function verifyLicense()
    {
        $license_notifications_array = aplVerifyLicense('', true);

        if ($license_notifications_array['notification_case'] == "notification_license_ok") {
            return json_encode(['success' => true, 'type' => $license_notifications_array['notification_data']]);
        } else {
            return json_encode(['success' => false, 'error' => $license_notifications_array['notification_text']]);
        }
    }

    //uninstall license
    public function uninstallLicense()
    {
        $license_notifications_array = aplUninstallLicense('');

        if ($license_notifications_array['notification_case'] == "notification_license_ok") {
            return json_encode(['success' => true]);
        } else {
            return json_encode(['success' => false, 'error' => $license_notifications_array['notification_text']]);
        }
    }

    /**
     * Show income page
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function income()
    {
        $plans = DB::table('user_plans')
            ->select('user_plans.*', 'users.username')
            ->join('users', 'user_plans.user_id', 'users.id')
            ->get();

        return view('admin.income', [
            'page' => 'Income',
            'plans' => $plans,
        ]);
    }

    /**
     * Show signaling server page
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function signaling()
    {
        $url = getSetting('SIGNALING_URL');

        return view('admin.signaling', [
            'page' => 'Signaling Server',
            'url' => $url,
        ]);
    }

    //check signaling status
    public function checkSignaling()
    {
        $url = getSetting('SIGNALING_URL');
        $status = 'Running';

        try {
            get_headers($url);
        } catch (\Exception $e) {
            $status = 'Unreachable';
        }

        return json_encode(['status' => $status]);
    }
}
