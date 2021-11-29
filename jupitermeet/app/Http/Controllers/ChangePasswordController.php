<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the change password form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('change-password.index', [
            'page' => 'Change Password',
        ]);
    }

    //change password
    public function changePassword(Request $request)
    {
        $request->validate([
            'current' => 'required',
            'new' => 'required|string|min:6|same:new',
            'confirm' => 'required|same:new',
        ]);

        $user = Auth::user();

        if (Hash::check($request->current, $user->password)) {
            $user->password = Hash::make($request->new);
            $user->save();
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }
}
