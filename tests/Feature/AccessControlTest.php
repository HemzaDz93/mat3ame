<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, array $attributes = []): User
    {
        static $seq = 0;
        $seq++;

        return User::factory()->create(array_merge([
            'username' => $role.$seq,
            'name' => 'مستخدم '.$role,
            'role' => $role,
            'is_active' => true,
            'password' => 'Password123',
        ], $attributes));
    }

    public function test_guests_are_redirected_to_the_login_screen(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('trainees.index'))->assertRedirect(route('login'));
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    public function test_staff_can_reach_administrative_screens(): void
    {
        $staff = $this->user(User::ROLE_STAFF);

        $this->actingAs($staff)->get(route('dashboard'))->assertOk();
        $this->actingAs($staff)->get(route('trainees.index'))->assertOk();
        $this->actingAs($staff)->get(route('employees.index'))->assertOk();
        $this->actingAs($staff)->get(route('payments.index'))->assertOk();
    }

    public function test_staff_cannot_reach_admin_only_screens(): void
    {
        $staff = $this->user(User::ROLE_STAFF);

        $this->actingAs($staff)->get(route('users.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('settings.edit'))->assertForbidden();
        $this->actingAs($staff)->get(route('logs.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('backups.index'))->assertForbidden();
    }

    public function test_supervisor_cannot_reach_admin_only_screens(): void
    {
        $supervisor = $this->user(User::ROLE_SUPERVISOR);

        $this->actingAs($supervisor)->get(route('users.index'))->assertForbidden();
        $this->actingAs($supervisor)->get(route('settings.edit'))->assertForbidden();
    }

    public function test_admin_can_reach_admin_only_screens(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);

        $this->actingAs($admin)->get(route('users.index'))->assertOk();
        $this->actingAs($admin)->get(route('settings.edit'))->assertOk();
        $this->actingAs($admin)->get(route('logs.index'))->assertOk();
        $this->actingAs($admin)->get(route('backups.index'))->assertOk();
    }

    public function test_canteen_can_only_reach_the_canteen_screen(): void
    {
        $canteen = $this->user(User::ROLE_CANTEEN);

        $this->actingAs($canteen)->get(route('canteen.index'))->assertOk();
        $this->actingAs($canteen)->get(route('trainees.index'))->assertForbidden();
        $this->actingAs($canteen)->get(route('employees.index'))->assertForbidden();
        $this->actingAs($canteen)->get(route('payments.index'))->assertForbidden();
    }

    public function test_only_admin_and_staff_can_reach_the_import_screen(): void
    {
        $this->actingAs($this->user(User::ROLE_ADMIN))->get(route('import.form'))->assertOk();
        $this->actingAs($this->user(User::ROLE_STAFF))->get(route('import.form'))->assertOk();
        $this->actingAs($this->user(User::ROLE_SUPERVISOR))->get(route('import.form'))->assertForbidden();
    }

    public function test_only_admin_and_supervisor_can_reach_canteen_reports(): void
    {
        $this->actingAs($this->user(User::ROLE_ADMIN))->get(route('canteen.report'))->assertOk();
        $this->actingAs($this->user(User::ROLE_SUPERVISOR))->get(route('canteen.report'))->assertOk();
        $this->actingAs($this->user(User::ROLE_STAFF))->get(route('canteen.report'))->assertForbidden();
        $this->actingAs($this->user(User::ROLE_CANTEEN))->get(route('canteen.report'))->assertForbidden();
    }

    public function test_deactivated_user_is_logged_out_on_the_next_request(): void
    {
        $admin = $this->user(User::ROLE_ADMIN, ['is_active' => false]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_everyone_can_manage_their_own_account(): void
    {
        $this->actingAs($this->user(User::ROLE_CANTEEN))->get(route('account.edit'))->assertOk();
        $this->actingAs($this->user(User::ROLE_STAFF))->get(route('account.edit'))->assertOk();
    }

    public function test_notifications_are_reachable_for_any_active_user(): void
    {
        $this->actingAs($this->user(User::ROLE_CANTEEN))->get(route('notifications.index'))->assertOk();
    }
}
