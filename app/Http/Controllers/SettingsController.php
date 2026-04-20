<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('settings.index', compact('user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
        ]);

        $user = auth()->user();
        $user->update($request->only(['name', 'email', 'phone', 'role', 'department']));

        return response()->json([
            'success' => true, 
            'message' => 'Personnel Records Synchronized'
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        auth()->user()->update([
            'password' => \Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Security Credentials Updated'
        ]);
    }
}
