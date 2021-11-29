<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use Illuminate\Support\Facades\DB;

class MeetingController extends Controller
{
    /**
     * Show all the meetings.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $meetings = DB::table('meetings')
            ->select('meetings.*', 'users.username')
            ->join('users', 'meetings.user_id', 'users.id')
            ->get();

        return view('admin.meeting.index', [
            'page' => 'Meetings',
            'meetings' => $meetings,
        ]);
    }

    //udpate meeting status
    public function updateMeetingStatus(Request $request)
    {
        $meeting = Meeting::find($request->id);
        $meeting->status = $request->checked == 'true' ? 'active' : 'inactive';

        if ($meeting->save()) {
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }
    
    //delete meeting
    public function deleteMeeting (Request $request) {
        $meeting = Meeting::find($request->id);
        
        if ($meeting->delete()) {
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }
}
