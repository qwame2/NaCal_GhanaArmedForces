<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuppliersTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_admin_accessing_suppliers_redirects_to_custom_suppliers()
    {
        $user = User::create([
            'name' => 'Main Admin User',
            'username' => 'admin_user',
            'role' => 'Main Admin',
            'is_admin' => true,
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('Password123'),
        ]);

        $this->actingAs($user);

        // Accessing the regular /admin/suppliers route should redirect to the custom page
        $response = $this->get('/admin/suppliers');
        $response->assertRedirect('/admin/head-of-admin/suppliers');

        // Accessing the custom /admin/head-of-admin/suppliers route should load successfully (status 200)
        $response2 = $this->get('/admin/head-of-admin/suppliers');
        $response2->assertStatus(200);
        $response2->assertViewIs('admin.admin_suppliers');
    }

    public function test_head_of_stores_accessing_suppliers_does_not_redirect()
    {
        $user = User::create([
            'name' => 'Head of Stores User',
            'username' => 'stores_user',
            'role' => 'Head of Stores',
            'is_admin' => true,
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('Password123'),
        ]);

        $this->actingAs($user);

        // Accessing the regular /admin/suppliers route should load successfully without redirect
        $response = $this->get('/admin/suppliers');
        $response->assertStatus(200);
        $response->assertViewIs('admin.suppliers');

        // Accessing the custom /admin/head-of-admin/suppliers route should abort with 403
        $response2 = $this->get('/admin/head-of-admin/suppliers');
        $response2->assertStatus(403);
    }
}
