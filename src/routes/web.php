<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionController;

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

Route::get('/login', [LoginController::class, 'create'])
    ->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])
    ->name('logout');

// Route::get('/verify-email', function () {
//     return view('auth.verify-email');
// })->middleware('auth')->name('verification.notice');

Route::get('/verify-email', function () {
    return view('auth.verify-email');
})->name('verification.notice');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    // 勤務スタート
    Route::post('/attendance/start', [AttendanceController::class, 'start']);
    // 勤務エンド
    Route::post('/attendance/end', [AttendanceController::class, 'end']);
    // 休憩スタート
    Route::post('/attendance/break/start', [AttendanceController::class, 'breakStart']);
    // 休憩エンド
    Route::post('/attendance/break/end', [AttendanceController::class, 'breakEnd']);

    // 勤怠一覧画面
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');
    // 勤怠詳細画面
    Route::get('/attendance/detail/{id}',[AttendanceController::class, 'show'])
        ->name('attendance.detail');

    // 勤怠修正申請
    Route::post('/attendance/{attendance}/correction', [CorrectionController::class, 'store'])
        ->name('attendance.correction');
    Route::get('/stamp_correction_request/list',
    [CorrectionController::class, 'index'])
        ->name('user.correction.list');
});