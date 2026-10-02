<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * I-load ang dashboard — lain-laing stats base sa role sa user.
     */
    public function __invoke(): Response
    {
        $user = Auth::user()->load('downlines');

        // Stats nga makita sa tanan nga roles
        $stats = $this->getMemberStats($user);

        // Extra stats para sa admin — tibuok platform overview
        if ($user->hasRole('admin')) {
            $stats = array_merge($stats, $this->getAdminStats());
        }

        // Recent members — admin makakita sa tanan, member makakita sa iyang downline lang
        $recentMembers = $this->getRecentMembers($user);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentMembers' => $recentMembers,
            'inviteLink' => $user->inviteLink(),
        ]);
    }

    /**
     * Stats nga makita sa matag member — base sa ilang sariling network.
     *
     * @return array<string, mixed>
     */
    private function getMemberStats(User $user): array
    {
        return [
            'directDownlines' => $user->downlines()->count(),
            'totalDownlines' => $this->countAllDownlines($user->id),
        ];
    }

    /**
     * Admin-only stats — tibuok platform.
     *
     * @return array<string, mixed>
     */
    private function getAdminStats(): array
    {
        $now = now();

        return [
            'totalMembers' => User::count(),
            'newMembersMonth' => User::whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->count(),
            'activeMembers' => User::where('status', 'active')->count(),
            'suspendedMembers' => User::where('status', 'suspended')->count(),
        ];
    }

    /**
     * Bag-ong members — admin makakita sa tanan, member sa iyang downline lang.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getRecentMembers(User $user): array
    {
        $query = $user->hasRole('admin')
            ? User::with('upline:id,name')
                ->latest()
                ->limit(10)
            : User::with('upline:id,name')
                ->where('referred_by', $user->id)
                ->latest()
                ->limit(10);

        return $query->get()
            ->map(fn (User $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'status' => $member->status,
                'upline' => $member->upline?->name ?? '—',
                'roles' => $member->getRoleNames(),
                'joined' => $member->created_at->diffForHumans(),
                'joined_date' => $member->created_at->format('M d, Y'),
            ])
            ->toArray();
    }

    /**
     * I-count ang tanan nga downlines recursively — direct ug indirect.
     */
    private function countAllDownlines(int $userId): int
    {
        // I-count recursively gamit ang raw query para efficient
        $directIds = User::where('referred_by', $userId)->pluck('id');

        if ($directIds->isEmpty()) {
            return 0;
        }

        $count = $directIds->count();

        foreach ($directIds as $id) {
            $count += $this->countAllDownlines($id);
        }

        return $count;
    }
}
