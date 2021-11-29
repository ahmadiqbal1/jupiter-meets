<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserPlan;
use Carbon\Carbon;
use Mail;
use App\Mail\PlanValidity;

class CheckPlanValidity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CheckPlanValidity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks plans validity';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::where('plan_type', 'paid')
            ->where('role', 'end-user')
            ->get();

        foreach ($users as $user) {
            $userPlan = UserPlan::where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->first();

            $dateToday = Carbon::now()->subDay(1)->toDateString();

            if ($dateToday == $userPlan->plan_end_date) {
                User::where('id', $user->id)->update(['plan_type' => 'free', 'plan_status' => 'expired']);
                Mail::to($user->email)->send(new PlanValidity($user->username));
            }
        }
    }
}
