<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function showAuth()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.auth');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-zA-Z])(?=.*[\d\W_]).+$/'
            ],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ], [
            'username.unique' => 'The personnel callsign "@' . $request->username . '" has already been registered in the database.',
            'password.regex' => 'The password must contain at least one letter and one number or symbol.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        try {
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'avatar' => $avatarPath,
            ]);

            Auth::login($user);
            $user->update(['last_login_at' => now()]);

            return redirect()->route('dashboard')->with('success', 'Registry initialized. Welcome, ' . $user->name);
        } catch (\Exception $e) {
            return back()->with('error', 'Critical System Failure: ' . $e->getMessage())->withInput();
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            auth()->user()->update(['last_login_at' => now()]);
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
