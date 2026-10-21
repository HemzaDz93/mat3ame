<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'username' => 'admin',
            'name' => 'مدير النظام',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'password' => 'Password123',
        ], $attributes));
    }

    public function test_login_screen_is_rendered_for_guests(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = $this->makeUser();

        $response = $this->post(route('login.attempt'), [
            'username' => 'admin',
            'password' => 'Password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_canteen_user_is_redirected_to_canteen_screen(): void
    {
        $this->makeUser(['username' => 'canteen', 'role' => User::ROLE_CANTEEN]);

        $this->post(route('login.attempt'), [
            'username' => 'canteen',
            'password' => 'Password123',
        ])->assertRedirect(route('canteen.index'));
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $this->makeUser();

        $this->from(route('login'))
            ->post(route('login.attempt'), [
                'username' => 'admin',
                'password' => 'كلمة-خاطئة',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $this->makeUser(['is_active' => false]);

        $this->from(route('login'))
            ->post(route('login.attempt'), [
                'username' => 'admin',
                'password' => 'Password123',
            ])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        $this->makeUser();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.attempt'), [
                'username' => 'admin',
                'password' => 'كلمة-خاطئة',
            ]);
        }

        $response = $this->post(route('login.attempt'), [
            'username' => 'admin',
            'password' => 'كلمة-خاطئة',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
