<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

//home route
Route::get('/', function () {
    return view('home', [
        'page' => 'Home',
    ]);
})->name('home');

//check if auth mode is enabled
Route::middleware('checkAuthMode')->group(function () {
	Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
	Route::get('profile', [App\Http\Controllers\DashboardController::class, 'profile'])->name('profile');
	Route::get('pricing', [App\Http\Controllers\PaymentController::class, 'index'])->name('pricing');
	Route::get('payment', [App\Http\Controllers\PaymentController::class, 'payment'])->name('payment');
    Route::post('handlePayment', [App\Http\Controllers\PaymentController::class, 'handlePayment'])->name('handlePayment');
});

//admin routes
Route::middleware('checkAdmin')->group(function () {
    Route::get('admin', [App\Http\Controllers\AdminController::class, 'index'])->name('admin');
    Route::get('income', [App\Http\Controllers\AdminController::class, 'income'])->name('income');
    Route::get('update', [App\Http\Controllers\AdminController::class, 'update'])->name('update');
    Route::get('check-for-update', [App\Http\Controllers\AdminController::class, 'checkForUpdate']);
    Route::get('download-update', [App\Http\Controllers\AdminController::class, 'downloadUpdate']);
    Route::get('license', [App\Http\Controllers\AdminController::class, 'license'])->name('license');
    Route::get('verify-license', [App\Http\Controllers\AdminController::class, 'verifyLicense']);
    Route::get('uninstall-license', [App\Http\Controllers\AdminController::class, 'uninstallLicense']);
    Route::get('signaling', [App\Http\Controllers\AdminController::class, 'signaling'])->name('signaling');
    Route::get('check-signaling', [App\Http\Controllers\AdminController::class, 'checkSignaling']);
    
    //meeting routes
    Route::get('meetings', [App\Http\Controllers\MeetingController::class, 'index'])->name('meetings');
    Route::post('update-meeting-status', [App\Http\Controllers\MeetingController::class, 'updateMeetingStatus']);
    Route::post('delete-meeting-admin', [App\Http\Controllers\MeetingController::class, 'deleteMeeting']);
    
    //user routes
    Route::get('users', [App\Http\Controllers\UserController::class, 'index'])->name('users');
    Route::post('update-user-status', [App\Http\Controllers\UserController::class, 'updateUserStatus']);
    Route::post('delete-user', [App\Http\Controllers\UserController::class, 'deleteUser']);
    Route::get('users/create', [App\Http\Controllers\UserController::class, 'createUserForm'])->name('createUser');
    Route::post('create-user', [App\Http\Controllers\UserController::class, 'createUser']);

    //global config routes
    Route::get('global-config', [App\Http\Controllers\GlobalConfigController::class, 'index'])->name('global-config');
    Route::get('global-config/edit/{id}', [App\Http\Controllers\GlobalConfigController::class, 'edit']);
    Route::post('update-global-config', [App\Http\Controllers\GlobalConfigController::class, 'update']);
    
    //content routes
    Route::get('content', [App\Http\Controllers\ContentController::class, 'index'])->name('content');
    Route::get('content/edit/{id}', [App\Http\Controllers\ContentController::class, 'edit']);
    Route::post('update-content', [App\Http\Controllers\ContentController::class, 'update']);
});

//change password
Route::get('change-password', [App\Http\Controllers\ChangePasswordController::class, 'index'])->name('changePassword');
Route::post('update-password', [App\Http\Controllers\ChangePasswordController::class, 'changePassword']);

//general routes
Route::post('create-meeting', [App\Http\Controllers\DashboardController::class, 'createMeeting']);
Route::post('delete-meeting', [App\Http\Controllers\DashboardController::class, 'deleteMeeting']);
Route::post('edit-meeting', [App\Http\Controllers\DashboardController::class, 'editMeeting']);
Route::post('send-invite', [App\Http\Controllers\DashboardController::class, 'sendInvite']);
Route::get('get-invites', [App\Http\Controllers\DashboardController::class, 'getInvites']);
Route::get('meeting/{id}', [App\Http\Controllers\DashboardController::class, 'meeting'])->middleware('checkPlan');
Route::post('check-meeting', [App\Http\Controllers\DashboardController::class, 'checkMeeting']);
Route::post('check-meeting-password', [App\Http\Controllers\DashboardController::class, 'checkMeetingPassword']);
Route::get('get-details', [App\Http\Controllers\DashboardController::class, 'getDetails']);

//extra routes
Route::get('privacy-policy', function () {
    return view('privacy-policy', [
        'page' => 'Privacy Policy',
    ]);
})->name('privacyPolicy');

Route::get('terms-and-conditions', function () {
    return view('terms-and-conditions', [
        'page' => 'Terms & Conditions',
    ]);
})->name('termsAndConditions');
