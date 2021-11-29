<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\User;
use App\Models\UserPlan;
use Illuminate\Support\Facades\DB;
use App\Mail\MeetingInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use stdClass;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['meeting', 'checkMeeting', 'checkMeetingPassword', 'getDetails']]);
        $this->middleware('checkPaymentMode', ['only' => ['profile']]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $meetings = DB::table('meetings')
            ->where('user_id', Auth::id())
            ->orderBy('id', 'DESC')
            ->get();

        return view('dashboard', [
            'page' => 'Dashboard',
            'meetings' => $meetings,
            'firstMeeting' => !$meetings->isEmpty() ? $meetings[0] : [],
        ]);
    }

    //create a new meeting
    public function createMeeting(Request $request)
    {
        $request->validate([
            'meeting_id' => 'required|unique:meetings',
            'title' => 'required|max:100',
            'description' => 'max:1000',
        ]);

        if (Auth::user()->plan_type == "free" && getSetting('PAYMENT_MODE') == 'enabled' && Meeting::where('user_id', Auth::id())->count() >= getSetting('MEETING_LIMIT')) {
            return json_encode(['success' => false, 'error' => 'You have reached the maximum meeting creation limit. Upgrade now!']);
        }

        $meeting = new Meeting();
        $meeting->meeting_id = $request->meeting_id;
        $meeting->title = $request->title;
        $meeting->description = $request->description;
        $meeting->user_id = Auth::id();
        $meeting->password = $request->password;

        if ($meeting->save()) {
            return json_encode(['success' => true, 'data' => $meeting]);
        }

        return json_encode(['success' => false]);
    }

    //delete a meeting
    public function deleteMeeting(Request $request)
    {
        $meeting = Meeting::find($request->id);

        if ($meeting->delete()) {
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }

    //edit a meeting
    public function editMeeting(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $meeting = Meeting::find($request->id);
        $meeting->title = $request->title;
        $meeting->description = $request->description;
        $meeting->password = $request->password;

        if ($meeting->save()) {
            return json_encode(['success' => true, 'data' => $meeting]);
        }

        return json_encode(['success' => false]);
    }

    //send an email invite
    public function sendInvite(Request $request)
    {
        $request->validate([
            'email' => 'required',
        ]);

        $meeting = Meeting::find($request->id);
        $meeting->invites .= $meeting->invites ? ',' . $request->email : $request->email;

        if ($meeting->save()) {
            Mail::to($request->email)->send(new MeetingInvitation($meeting));
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }

    //get all the invites associated with the meeting
    public function getInvites(Request $request)
    {
        $meeting = Meeting::find($request->id);

        if ($meeting) {
            return json_encode(['success' => true, 'data' => $meeting->invites ? explode(',', $meeting->invites) : []]);
        }

        return json_encode(['success' => false]);
    }

    //check if the meeting exist
    public function checkMeeting(Request $request)
    {
        if (getSetting('AUTH_MODE') == 'disabled') {
            return json_encode(['success' => true, 'id' => $request->id]);
        }

        $meeting = Meeting::where(['meeting_id' => $request->id, 'status' => 'active'])->first();

        if ($meeting) {
            return json_encode(['success' => true, 'id' => $request->id]);
        }

        return json_encode(['success' => false]);
    }

    /**
     * Show the meeting page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function meeting($id)
    {
        $meeting = new \stdClass();

        if (getSetting('AUTH_MODE') == 'enabled') {
            $meeting = Meeting::where(['meeting_id' => $id, 'status' => 'active'])->first();

            if (!$meeting) {
                return redirect('/')->withErrors('The meeting does not exist');
            }    
            
            $planType = User::find($meeting->user_id)->plan_type;
        } else {
            $planType = 'free';
            $meeting->title = 'Meeting';
            $meeting->meeting_id = $id;
            $meeting->description = '-';
            $meeting->password = null;
            $meeting->user_id = 0;
        }

        $meeting->isModerator = Auth::user() && getSetting('MODERATOR_RIGHTS') == "enabled"  ? Auth::user()->id == $meeting->user_id : false;
        $meeting->limitedTimeMeeting = $planType == 'free';
        $meeting->username = Auth::user() ? Auth::user()->username . ($meeting->isModerator ? ' (Moderator)' : '') : '';

        return view('meeting', [
            'page' => 'Meeting',
            'meeting' => $meeting,
        ]);
    }

    //check if meeting password is valid or not
    public function checkMeetingPassword(Request $request)
    {
        $meeting = Meeting::find($request->id);

        if ($meeting->password == $request->password) {
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }

    //show profile page with transaction details
    public function profile() {
        $userPlan = UserPlan::where('user_id', Auth::id())
                ->orderBy('id', 'desc')
                ->get();

        return view('profile', [
            'page' => 'Profile',
            'userPlan' => $userPlan
        ]);
    }

    //get the application details and send it to the user
    public function getDetails()
    {
        $details = new stdClass();
        $details->timeLimit = getSetting('TIME_LIMIT');
        $details->stunUrl = getSetting('STUN_URL');
        $details->turnUrl = getSetting('TURN_URL');
        $details->turnUsername = getSetting('TURN_USERNAME');
        $details->turnPassword = getSetting('TURN_PASSWORD');
        $details->defaultUsername = getSetting('DEFAULT_USERNAME');
        $details->appName = getSetting('APPLICATION_NAME');
        $details->signalingURL = getSetting('SIGNALING_URL');
        $details->authMode = getSetting('AUTH_MODE');
        $details->moderatorRights = getSetting('MODERATOR_RIGHTS');

        return json_encode(['success' => true, 'data' => $details]);
    }
}
