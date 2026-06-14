<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\LoginController;
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\User\AttendanceController;
use App\Http\Controllers\User\CorrectionController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\AttendanceRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes GETfor your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 一般ユーザーログイン
Route::get('/login', [LoginController::class, 'create'])
    ->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])
    ->name('logout');
Route::get('/verify-email', function () {
    return view('user.auth.verify-email');})
    ->name('verification.notice');

// 一般ユーザーミドルウェア
Route::middleware(['auth', 'verified'])->group(function () {
    // 勤怠登録
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');
    Route::post('/attendance/start', [AttendanceController::class, 'start']);
    Route::post('/attendance/end', [AttendanceController::class, 'end']);
    Route::post('/attendance/break/start', [AttendanceController::class, 'breakStart']);
    Route::post('/attendance/break/end', [AttendanceController::class, 'breakEnd']);

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    // 勤怠詳細
    Route::get('/attendance/detail/{id}',[AttendanceController::class, 'show'])
        ->name('attendance.detail');

    // 勤怠修正申請
    Route::post('/attendance/correction', [CorrectionController::class, 'store'])
        ->name('attendance.correction');

    // 勤怠申請一覧
    Route::get('/stamp_correction_request/list', [CorrectionController::class, 'index'])
        ->name('user.correction.list');
});

// 管理者ログイン
Route::get('/admin/login', [AdminLoginController::class, 'create'])
        ->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'store'])
        ->name('admin.login.store');

// 管理者ミドルウェア
Route::middleware(['auth', 'admin'])->group(function () {
    // 勤怠一覧
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('admin.attendance.list');
    
    // スタッフ別勤怠一覧
    Route::get('/admin/attendance/staff/{id}', [StaffController::class, 'attendance'])
        ->name('admin.staff.attendance');
    Route::get('/admin/attendance/staff/{id}/csv', [StaffController::class, 'exportCsv'])
        ->name('admin.staff.attendance.csv');

    // 勤怠詳細
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])
        ->name('admin.attendance.detail');
    Route::put('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])
        ->name('admin.attendance.update');

    // スタッフ一覧
    Route::get('/admin/staff/list', [StaffController::class, 'index'])
        ->name('admin.staff.list');

    // 申請・承認
    Route::get('/admin/stamp_correction_request/list', [AttendanceRequestController::class, 'index'])
        ->name('admin.correction.list');
    Route::get('/admin/stamp_correction_request/approve/{attendance_correct_request_id}', [AttendanceRequestController::class, 'show'])
        ->name('admin.correction.approve');
    Route::post('/admin/stamp_correction_request/approve/{attendance_correct_request_id}', [AttendanceRequestController::class, 'approve'])
        ->name('admin.correction.approve.store');
});