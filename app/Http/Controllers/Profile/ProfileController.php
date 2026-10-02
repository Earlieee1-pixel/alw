<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * I-show ang profile page sa current user.
     */
    public function index(): Response
    {
        $user = Auth::user()->load('upline:id,name', 'downlines');

        return Inertia::render('Profile/Index', [
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'roles' => $user->getRoleNames(),
                'invite_code' => $user->invite_code,
                'invite_link' => $user->inviteLink(),
                'upline' => $user->upline?->name ?? '—',
                'downlines' => $user->downlines->count(),
                'joined' => $user->created_at->format('M d, Y'),
            ],
        ]);
    }

    /**
     * I-update ang name ug email sa user.
     */
    public function updateInfo(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * I-update ang password sa user.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    /**
     * I-regenerate ang invite code sa user — ang daan nga code dili na valid.
     * Para sa security — kung naa nag-leak ang code, ma-reset dayon.
     */
    public function regenerateInviteCode(): RedirectResponse
    {
        $user = Auth::user();
        $newCode = User::generateUniqueInviteCode();
        $user->invite_code = $newCode;
        $user->save();

        return back()->with('success', 'Invite link regenerated. Your old link is now invalid.');
    }
}
