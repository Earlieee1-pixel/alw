<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\User;
use App\Models\Video;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * I-show ang admin settings page — platform overview ug stats.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Settings', [
            'stats' => [
                'total_members' => User::count(),
                'active_members' => User::where('status', 'active')->count(),
                'suspended' => User::where('status', 'suspended')->count(),
                'total_videos' => Video::where('is_published', true)->count(),
                'total_rooms' => Room::count(),
                'active_rooms' => Room::where('status', 'active')->count(),
                'new_this_month' => User::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ],
            'app' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'env' => config('app.env'),
                'version' => app()->version(),
            ],
            // Role distribution — para sa overview
            'roleStats' => [
                'admins' => User::role('admin')->count(),
                'leaders' => User::role('leader')->count(),
                'members' => User::role('member')->count(),
            ],
        ]);
    }
}
