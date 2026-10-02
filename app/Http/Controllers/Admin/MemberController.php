<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AccountStatusChanged;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    /**
     * I-show ang lista sa tanan nga members — may search ug filter.
     */
    public function index(Request $request): Response
    {
        $members = User::with('upline:id,name')
            ->withCount('downlines')
            ->when($request->search, function ($query, $search) {
                // I-search base sa name o email
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->role, fn ($q, $r) => $q->role($r))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Members/Index', [
            'members' => $members->through(fn (User $user) => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'status'      => $user->status,
                'roles'       => $user->getRoleNames(),
                'upline'      => $user->upline?->name ?? '—',
                'downlines'   => $user->downlines_count,
                'invite_code' => $user->invite_code,
                'joined'      => $user->created_at->format('M d, Y'),
                'joined_ago'  => $user->created_at->diffForHumans(),
            ]),
            'filters' => $request->only(['search', 'status', 'role']),
            'totals'  => [
                'all'       => User::count(),
                'active'    => User::where('status', 'active')->count(),
                'suspended' => User::where('status', 'suspended')->count(),
                'inactive'  => User::where('status', 'inactive')->count(),
            ],
        ]);
    }

    /**
     * I-activate ang account sa member.
     */
    public function activate(User $user): RedirectResponse
    {
        $user->update(['status' => 'active']);

        // I-notify ang member nga na-activate na ang iyang account
        $user->notify(new AccountStatusChanged('active'));

        return back()->with('success', "{$user->name}'s account has been activated.");
    }

    /**
     * I-suspend ang account sa member.
     */
    public function suspend(User $user): RedirectResponse
    {
        $user->update(['status' => 'suspended']);

        // I-notify ang member nga na-suspend ang iyang account
        $user->notify(new AccountStatusChanged('suspended'));

        return back()->with('success', "{$user->name}'s account has been suspended.");
    }

    /**
     * I-change ang role sa member.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'in:admin,leader,member'],
        ]);

        // I-sync ang role — i-remove ang daan, i-assign ang bag-o
        $user->syncRoles([$request->role]);

        return back()->with('success', "{$user->name}'s role updated to {$request->role}.");
    }
}
