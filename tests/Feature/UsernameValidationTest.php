<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsernameValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::updateOrCreate(
            ['key' => 'allow_personnel_registration'],
            ['value' => '1', 'type' => 'boolean', 'group' => 'system']
        );
    }

    /**
     * @dataProvider invalidUsernamesProvider
     */
    public function test_self_registration_rejects_special_characters_in_username(string $invalidUsername): void
    {
        $response = $this->post(route('self-register'), [
            'name' => 'John Doe',
            'username' => $invalidUsername,
            'phone' => '+233241234567',
            'service_number' => 'JD-1234',
            'department' => 'Audit Department',
            'password' => 'ValidPassword123',
            'password_confirmation' => 'ValidPassword123',
        ]);

        $response->assertSessionHasErrors(['username']);
        $errors = session('errors')->get('username');
        $this->assertContains('Username must not contain special characters.', $errors);

        $this->assertDatabaseMissing('users', [
            'username' => $invalidUsername,
        ]);
    }

    /**
     * @dataProvider invalidUsernamesProvider
     */
    public function test_json_self_registration_returns_422_with_clear_message(string $invalidUsername): void
    {
        $response = $this->postJson(route('self-register'), [
            'name' => 'John Doe',
            'username' => $invalidUsername,
            'phone' => '+233241234567',
            'service_number' => 'JD-1234',
            'department' => 'Audit Department',
            'password' => 'ValidPassword123',
            'password_confirmation' => 'ValidPassword123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'username' => 'Username must not contain special characters.',
        ]);
    }

    public static function invalidUsernamesProvider(): array
    {
        return [
            'contains at sign' => ['john@123'],
            'contains dot' => ['john.doe'],
            'contains dash' => ['john-doe'],
            'contains underscore' => ['john_doe'],
            'contains space' => ['john 123'],
            'contains exclamation' => ['john!doe'],
            'contains hash' => ['john#123'],
        ];
    }

    /**
     * @dataProvider validUsernamesProvider
     */
    public function test_self_registration_accepts_valid_alphanumeric_usernames(string $validUsername): void
    {
        $response = $this->post(route('self-register'), [
            'name' => 'John Doe',
            'username' => $validUsername,
            'phone' => '+233241234567',
            'service_number' => 'JD-1234',
            'department' => 'Audit Department',
            'password' => 'ValidPassword123',
            'password_confirmation' => 'ValidPassword123',
        ]);

        $response->assertSessionDoesntHaveErrors(['username']);
        $this->assertDatabaseHas('users', [
            'username' => $validUsername,
        ]);
    }

    public static function validUsernamesProvider(): array
    {
        return [
            'john123' => ['john123'],
            'johndoe123' => ['johndoe123'],
            'uppercase and numbers' => ['JohnDoe99'],
            'letters only' => ['johndoe'],
        ];
    }

    public function test_admin_registration_rejects_special_characters_in_username(): void
    {
        // Ensure no active admin exists so registration is open
        User::where('is_admin', true)->delete();

        $response = $this->post(route('register'), [
            'role' => 'Head of Stores',
            'name' => 'Admin User',
            'username' => 'admin@123',
            'rank' => 'SNCO',
            'service_number' => 'JD-9999',
            'password' => 'AdminPass123',
            'password_confirmation' => 'AdminPass123',
        ]);

        $response->assertSessionHasErrors(['username']);
        $errors = session('errors')->get('username');
        $this->assertContains('Username must not contain special characters.', $errors);

        $this->assertDatabaseMissing('users', [
            'username' => 'admin@123',
        ]);
    }

    public function test_registration_view_contains_frontend_validation_elements(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);

        // Check validation elements
        $response->assertSee('username-error-msg', false);
        $response->assertSee('Username must not contain special characters.', false);
        $response->assertSee('username-has-error', false);
        $response->assertSee('initUsernameValidation', false);
    }
}
