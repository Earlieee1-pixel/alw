<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    /**
     * I-show ang login page.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * I-validate ug i-authenticate ang user.
     */
    public function store(Request $request): RedirectResponse
    {
        // I-validate ang input sa login form
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Attempt sa login — kung dili maka-login, ibalik ang error
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Dili tama ang email o password. Palihug sulayi pag-usab.',
            ])->onlyInput('email');
        }

        // Check kung active pa ang account
        if (! Auth::user()->isActive()) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Ang imong account kay suspended o inactive. Pangita ang imong upline.',
            ])->onlyInput('email');
        }

        // Regenerate session para ma-prevent ang session fixation
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
