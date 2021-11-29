<?php

namespace App\Http\Controllers;

use App\Mail\UserCreation;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Show all the users.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $users = DB::table('users')
            ->select('id', 'username', 'email', 'status', 'plan_type', 'plan_status', 'created_at')
            ->where('role', 'end-user')
            ->get();

        return view('admin.user.index', [
            'page' => 'Users',
            'users' => $users,
        ]);
    }

    //udpate user status
    public function updateUserStatus(Request $request)
    {
        $user = User::find($request->id);
        $user->status = $request->checked == 'true' ? 'active' : 'inactive';

        if ($user->save()) {
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }

    //delete user
    public function deleteUser(Request $request)
    {
        $user = User::find($request->id);

        if ($user->delete()) {
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }

    //show create user form
    public function createUserForm()
    {
        return view('admin.user.create', [
            'page' => 'Create User',
        ]);
    }

    //create user
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users|max:20|alpha_dash',
            'email' => 'required|unique:users|max:50',
            'password' => 'required|max:50',
        ]);

        if ($validator->fails()) {
            return json_encode(['success' => false, 'error' => $validator->errors()->first()]);
        }

        $model = new User();
        $model->username = $request->username;
        $model->email = $request->email;
        $model->password = Hash::make($request->password);
        $model->save();

        Mail::to($request->email)->send(new UserCreation($request->all()));
        return json_encode(['success' => true]);
    }
}
