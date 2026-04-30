<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access to Command Center.');
        }

        // Only fetch Standard Personnel for oversight
        $users = User::where('is_admin', false)->get();
        $totalUsers = User::where('is_admin', false)->count();
        $allUsers = User::all(); // Keep for calculating global metrics if needed
        $recentLogins = User::where('is_admin', false)->orderBy('last_login_at', 'desc')->limit(5)->get();
        
        return view('admin.index', compact('users', 'totalUsers', 'allUsers', 'recentLogins'));
    }

    public function updateUser(Request $request, $id)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string',
            'department' => 'nullable|string',
        ]);

        $user->update([
            'name' => $request->name,
            'role' => $request->role,
            'department' => $request->department,
            'is_admin' => $request->role === 'Admin',
        ]);

        return back()->with('success', "Personnel registry for {$user->name} updated successfully.");
    }

    public function destroyUser($id)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return back()->with('error', "Self-termination of admin session is prohibited.");
        }

        $user->delete();
        return back()->with('success', "Personnel record removed from the registry.");
    }
}
