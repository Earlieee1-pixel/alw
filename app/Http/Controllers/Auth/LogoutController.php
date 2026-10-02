<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * I-logout ang user ug i-redirect sa landing page.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        // I-logout ang current user
        Auth::logout();

        // I-invalidate ang session para ma-clear ang data
        $request->session()->invalidate();

        // Bag-ong CSRF token para sa security
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
