<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboard;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\MembersController;
use App\Http\Controllers\MembershipPlanController;
use App\Http\Controllers\authentications\LoginBasic;

Route::get('/', function () {
    return view('content.authentications.auth-login-basic');
})->name('login');

Route::post('/', [LoginBasic::class, 'login'])->name('auth.login');

//main page
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    Route::post('/dashboard/logout', [LoginBasic::class, 'logout'])->name('logout');
    Route::get('/dashboard/settings', [AdminDashboard::class, 'revenue'])->name('settings');

    // member management routes
    Route::prefix('/members')->group(function () {
        Route::get('/add-member', [MembersController::class, 'add'])->name('Members-add');
        Route::post('/add-member', [MembersController::class, 'store'])->name('Members-store');
        Route::get('/list', [MembersController::class, 'list'])->name('Members-list');
        Route::delete('/delete/{id}', [MembersController::class, 'destroy'])->name('members.destroy');
        Route::get('/show/{id}', [MembersController::class, 'show'])->name('members.show');
    });
    Route::prefix('/api')->group(function () {
        Route::get('/members/{id}', [MembersController::class, 'apiShow']);
        Route::put('/members/{id}', [MembersController::class, 'apiUpdate']);
    });

    Route::get('/plans', [MembershipPlanController::class, 'list'])->name('plans');
    Route::prefix('/plans')->group(function () {
        Route::post('/add', [MembershipPlanController::class, 'store'])->name('Membership_plans.store');
        Route::delete('/delete/{plan}', [MembershipPlanController::class, 'destroy'])->name('membership_plans.destroy');
        Route::Post('/edit/{plan}', [MembershipPlanController::class, 'edit'])->name('membership_plans.edit');
    });
    Route::get('/checkin/{member_id}', [CheckinController::class, 'processScan'])->name('Checkin');
});
