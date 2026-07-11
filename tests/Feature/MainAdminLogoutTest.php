<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MainAdminLogoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('main_admin_status')->default('pending');
            });
        } catch (\Exception $e) {}
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('origin_admin_status')->default('pending');
            });
        } catch (\Exception $e) {}
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('alternative_status')->nullable();
            });
        } catch (\Exception $e) {}
    }

    public function test_main_admin_navigation_after_offline_beacon()
    {
        // Create the main admin user
        $user = User::create([
            'name' => 'BEN 10',
            'username' => 'BENs',
            'role' => 'Main Admin',
            'is_admin' => true,
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('Password123'),
        ]);

        // 1. Post to login
        $loginResponse = $this->post('/login', [
            'username' => 'BENs',
            'password' => 'Password123',
        ]);

        $loginResponse->assertRedirect('/main-admin/requisitions');
        
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
        $nextPageResponse = $this->get('/main-admin/requisitions');

        // Assert that the page is loaded successfully (not redirected to login)
        $nextPageResponse->assertStatus(200);
        $this->assertTrue(auth()->check(), 'User should still be authenticated after page navigation/refresh');
    }
}
