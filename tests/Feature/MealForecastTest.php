<?php

namespace Tests\Feature;

use App\Models\Meal;
use App\Models\MealForecast;
use App\Models\Trainee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealForecastTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create([
            'username' => 'user_'.$role,
            'role' => $role,
            'is_active' => true,
            'password' => 'Password123',
        ]);
    }

    public function test_assistant_can_view_the_forecast_screen(): void
    {
        $this->actingAs($this->user(User::ROLE_ASSISTANT))
            ->get(route('forecast.index'))
            ->assertOk()
            ->assertSee('إعلان الوجبات');
    }

    public function test_admin_can_view_the_forecast_screen(): void
    {
        $this->actingAs($this->user(User::ROLE_ADMIN))
            ->get(route('forecast.index'))
            ->assertOk();
    }

    public function test_other_roles_cannot_reach_the_forecast_screen(): void
    {
        foreach ([User::ROLE_STAFF, User::ROLE_SUPERVISOR, User::ROLE_CANTEEN] as $role) {
            $this->actingAs($this->user($role))
                ->get(route('forecast.index'))
                ->assertForbidden();
        }
    }

    public function test_assistant_can_register_an_expected_count(): void
    {
        $assistant = $this->user(User::ROLE_ASSISTANT);
        $day = now()->toDateString();

        $this->actingAs($assistant)
            ->post(route('forecast.store'), ['day' => $day, 'expected_count' => 120])
            ->assertRedirect();

        $this->assertDatabaseHas('meal_forecasts', [
            'expected_count' => 120,
            'created_by' => $assistant->id,
        ]);
    }

    public function test_registering_the_same_day_updates_the_existing_record(): void
    {
        $assistant = $this->user(User::ROLE_ASSISTANT);
        $day = now()->toDateString();

        MealForecast::create(['day' => $day, 'expected_count' => 100, 'created_by' => $assistant->id]);

        $this->actingAs($assistant)
            ->post(route('forecast.store'), ['day' => $day, 'expected_count' => 150])
            ->assertRedirect();

        $this->assertSame(1, MealForecast::query()->whereDate('day', $day)->count());
        $this->assertSame(150, (int) MealForecast::forDay($day)->expected_count);
    }

    public function test_expected_count_is_shown_on_the_canteen_screen(): void
    {
        MealForecast::create(['day' => now()->toDateString(), 'expected_count' => 88]);

        $this->actingAs($this->user(User::ROLE_CANTEEN))
            ->get(route('canteen.index'))
            ->assertOk()
            ->assertSee('الوجبات المتوقّعة اليوم')
            ->assertSee('88');
    }

    public function test_expected_count_is_shown_in_the_canteen_report(): void
    {
        $today = now()->toDateString();

        MealForecast::create(['day' => $today, 'expected_count' => 77]);

        // وجبة متربص فعلية لنفس اليوم
        $trainee = Trainee::create([
            'reg_no' => '1001',
            'electronic_reg_no' => 'E1001',
            'last_name' => 'بلقاسم',
            'first_name' => 'ياسين',
        ]);

        Meal::create([
            'trainee_id' => $trainee->id,
            'day' => $today,
            'served_at' => now(),
        ]);

        $this->actingAs($this->user(User::ROLE_ADMIN))
            ->get(route('canteen.report', ['from' => $today, 'to' => $today]))
            ->assertOk()
            ->assertSee('الوجبات المتوقّعة (الفترة)')
            ->assertSee('المتوقّع (متربصون)');
    }
}
