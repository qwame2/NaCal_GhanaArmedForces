<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_navigation_after_offline_beacon()
    {
        // Create the officer user
        $user = User::create([
            'name' => 'Linda Atto',
            'username' => 'atto',
            'role' => 'Officer',
            'is_admin' => false,
            'is_active' => true,
            'department' => 'Stores',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);

        // 1. Post to login
        $loginResponse = $this->post('/login', [
            'username' => 'atto',
            'password' => 'password',
            'target_interface' => 'user',
        ]);

        $loginResponse->assertRedirect('/dashboard');
        
        // Assert that the user is now authenticated
        $this->assertTrue(auth()->check());
        $this->assertEquals($user->id, auth()->id());

        // 2. Simulate the exit beacon (page hide/unload)
        $beaconResponse = $this->post('/api/user/offline');
        $beaconResponse->assertStatus(200);

        // Assert is_online is now false in the database
        $user->refresh();
        $this->assertFalse($user->is_online);

        // 3. Request the dashboard again (using the same session)
        $nextPageResponse = $this->get('/dashboard');

        // Assert that the page is loaded successfully (not redirected to login)
        $nextPageResponse->assertStatus(200);
        $this->assertTrue(auth()->check(), 'User should still be authenticated after page navigation/refresh');
    }
}
