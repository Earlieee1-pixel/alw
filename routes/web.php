<?php

use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Call\CallController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Network\NetworkController;
use App\Http\Controllers\Network\TreeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Video\VideoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// =========================================================
// PUBLIC ROUTES — bisan kinsa pwede mu-access
// =========================================================

Route::get('/', function () {
    return Inertia::render('Landing');
})->name('home');

// =========================================================
// GUEST ROUTES — para sa dili pa naka-login
// =========================================================

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');

    // Login — 5 attempts per minute per IP
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');

    // Register — 3 attempts per minute per IP
    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:register')
        ->name('register.store');
});

// =========================================================
// AUTH ROUTES — para sa tanan nga naka-login
// =========================================================

Route::middleware('auth')->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Messages
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/start', [MessageController::class, 'startOrOpen'])->name('messages.start');

    // Message send — 30 per minute para dili ma-spam
    Route::post('/messages/{conversation}/send', [MessageController::class, 'send'])
        ->middleware('throttle:messages')
        ->name('messages.send');

    // Message poll — 30 per minute (every 3s = ~20/min)
    Route::get('/messages/{conversation}/poll', [MessageController::class, 'poll'])
        ->middleware('throttle:poll')
        ->name('messages.poll');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // Recent notifications — 60 per minute
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])
        ->middleware('throttle:notifications')
        ->name('notifications.recent');

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Profile — view ug update
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.update-info');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::post('/profile/regenerate-invite', [ProfileController::class, 'regenerateInviteCode'])->name('profile.regenerate-invite');

    // Network — downline tree (old dynamic tree, kept for reference)
    Route::get('/network', [NetworkController::class, 'index'])->name('network.index');

    // Binary tree — fixed 5-level shared tree, click to drill, hover to set name
    Route::get('/network/tree', [TreeController::class, 'index'])->name('network.tree');
    Route::post('/network/tree/set-name', [TreeController::class, 'setName'])->name('tree.set-name');
    Route::post('/network/tree/clear-name', [TreeController::class, 'clearName'])->name('tree.clear-name');

    // Training videos — members makakita ug manood
    Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
    Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');

    // Post + delete videos — admin/leader lang (checked sa controller)
    Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
    Route::delete('/videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');

    // Video call rooms — members makakita ug maka-join
    Route::get('/calls', [CallController::class, 'index'])->name('calls.index');
    Route::get('/calls/{room}', [CallController::class, 'show'])->name('calls.show');

    // Create + close rooms — admin/leader lang (checked sa controller)
    Route::post('/calls', [CallController::class, 'store'])->name('calls.store');
    Route::post('/calls/{room}/close', [CallController::class, 'close'])->name('calls.close');
});

// =========================================================
// ADMIN ROUTES — admin role lang ang makaka-access
// =========================================================

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Members management — view, activate, suspend, update role
    Route::get('/members', [AdminMemberController::class, 'index'])->name('members.index');
    Route::post('/members/{user}/activate', [AdminMemberController::class, 'activate'])->name('members.activate');
    Route::post('/members/{user}/suspend', [AdminMemberController::class, 'suspend'])->name('members.suspend');
    Route::post('/members/{user}/role', [AdminMemberController::class, 'updateRole'])->name('members.role');

    // Platform settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
});
