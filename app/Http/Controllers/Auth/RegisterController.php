<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\NewDownline;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    /**
     * I-show ang register page.
     * Kinahanglan ang valid ref code sa URL — kung wala, i-redirect sa login.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        $refCode = $request->query('ref');

        // Kung walay ref code sa URL, dili pwede mag-register
        if (! $refCode) {
            return redirect()->route('login')->withErrors([
                'ref' => 'Kinahanglan og invite code para makaparegister.',
            ]);
        }

        // I-check kung valid ang ref code — kinahanglan mag-exist sa database
        $inviter = User::where('invite_code', $refCode)
            ->where('status', 'active')
            ->first();

        if (! $inviter) {
            return redirect()->route('login')->withErrors([
                'ref' => 'Invalid o expired na ang invite code. Pangita ang imong upline.',
            ]);
        }

        // I-pass ang ref code ug inviter name sa Vue component
        return Inertia::render('Auth/Register', [
            'refCode' => $refCode,
            'inviterName' => $inviter->name,
        ]);
    }

    /**
     * I-process ang registration sa bag-ong member.
     */
    public function store(Request $request): RedirectResponse
    {
        // I-validate ang tanan nga fields
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'invite_code' => ['required', 'string', 'exists:users,invite_code'],
        ]);

        // Pangitaa ang upline base sa invite code
        $upline = User::where('invite_code', $validated['invite_code'])
            ->where('status', 'active')
            ->firstOrFail();

        // I-create ang bag-ong user — auto-generate ang iyang invite code sa model boot
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'referred_by' => $upline->id,
        ]);

        // I-assign ang default role nga member
        $user->assignRole('member');

        // I-notify ang upline — bag-ong member sa iyang network
        $upline->notify(new NewDownline($user));

        // I-login dayon human mag-register
        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
