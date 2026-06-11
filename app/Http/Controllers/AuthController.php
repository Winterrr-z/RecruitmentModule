<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Handle user logout and redirect based on role.
     */
    public function logout(Request $request)
    {
        $role = Auth::user()?->role;

        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($role === 'hr') {
            return redirect()->route('hr.login');
        }

        return redirect()->route('careers');
    }
}
