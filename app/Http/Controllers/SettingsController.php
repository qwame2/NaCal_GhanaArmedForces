<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $user = [
            'name' => 'John Doe', // Mocked user data
            'email' => 'admin@gaf.gov.gh',
            'role' => 'Principal Storekeeper',
            'phone' => '+233 24 555 1234',
            'department' => 'Logistics Department',
            'avatar_color' => '#6366f1',
        ];

        return view('settings.index', compact('user'));
    }

    public function update(Request $request)
    {
        return response()->json(['success' => true, 'message' => 'Profile updated successfully!']);
    }
}
