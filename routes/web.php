<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboard;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\MembersController;
use App\Http\Controllers\ReceptionDashboard;
use App\Http\Controllers\MembershipPlanController;

Route::get('/', function () {
    return view('content.authentications.auth-login-basic');
});

//main page
Route::middleware(['auth'])->group(function () {

    Route::middleware(['UserAccess:admin'])->group(function () {
        Route::get('/stats', [AdminDashboard::class, 'stats'])->name('stats');
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('admin.dashboard');
        Route::get('/dashboard/settings', [AdminDashboard::class, 'revenue'])->name('settings');
    });

    Route::middleware('UserAccess:receptionist')->group(function () {
        Route::get('/receptionist/dashboard', [ReceptionDashboard::class, 'index'])->name('receptionist.dashboard');
    });

    // member management routes
    Route::prefix('/members')->group(function () {
        Route::get('/add-member', [MembersController::class, 'add'])->name('Members-add');
        Route::post('/add-member', [MembersController::class, 'store'])->name('Members-store');
        Route::get('/list', [MembersController::class, 'list'])->name('Members-list');
        Route::delete('/delete/{id}', [MembersController::class, 'destroy'])
            ->middleware('UserAccess:admin')
            ->name('members.destroy');
        Route::get('/show/{id}', [MembersController::class, 'show'])->name('members.show');
    });
    Route::middleware(['UserAccess:admin'])->group(function () {
        Route::prefix('/users')->group(function () {
            Route::get('/add-users', [UserController::class, 'add'])->name('Users-add');
            Route::post('/add-users', [UserController::class, 'store'])->name('Users-store');
            Route::get('/list', [UserController::class, 'list'])->name('Users-list');
            Route::delete('users/delete/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });
    });
    Route::prefix('/api')->group(function () {
        Route::get('/members/{id}', [MembersController::class, 'apiShow']);
        Route::put('/members/{id}', [MembersController::class, 'apiUpdate']);
        Route::post('/members/{id}/renew-subscription', [MembersController::class, 'apiRenewSubscription']);
    });

    Route::middleware('UserAccess:admin')->group(function () {
        Route::get('/plans', [MembershipPlanController::class, 'list'])->name('plans');
        Route::prefix('/plans')->group(function () {
            Route::post('/add', [MembershipPlanController::class, 'store'])->name('Membership_plans.store');
            Route::delete('/delete/{plan}', [MembershipPlanController::class, 'destroy'])->name('membership_plans.destroy');
            Route::Post('/edit/{plan}', [MembershipPlanController::class, 'edit'])->name('membership_plans.edit');
        });
    });
    Route::get('/checkin/{member_id}', [CheckinController::class, 'processScan'])->name('Checkin');
    Route::get('/members/expiring', [MembersController::class, 'expiringSoon'])->name('Members-expiring');
});

Auth::routes();
