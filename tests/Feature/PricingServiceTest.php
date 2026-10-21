<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Services\PricingService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_prices_fall_back_to_meal_price_times_days(): void
    {
        $pricing = app(PricingService::class);

        // الافتراضيات: meal_price = 50 و days_per_month = 22
        $this->assertSame(50.0, $pricing->priceForPlan(Payment::PLAN_DAY));
        $this->assertSame(1100.0, $pricing->priceForPlan(Payment::PLAN_MONTH));
        $this->assertSame(3300.0, $pricing->priceForPlan(Payment::PLAN_QUARTER));
    }

    public function test_explicit_month_price_overrides_the_fallback(): void
    {
        app(SettingsService::class)->setMany([
            'meal_price' => '60',
            'days_per_month' => '20',
            'price_month' => '900',
        ]);

        $pricing = app(PricingService::class);

        $this->assertSame(60.0, $pricing->priceForPlan(Payment::PLAN_DAY));
        $this->assertSame(900.0, $pricing->priceForPlan(Payment::PLAN_MONTH));
        // الثلاثة أشهر بلا سعر صريح: 60 × 20 × 3
        $this->assertSame(3600.0, $pricing->priceForPlan(Payment::PLAN_QUARTER));
    }

    public function test_explicit_quarter_price_overrides_the_fallback(): void
    {
        app(SettingsService::class)->setMany(['price_quarter' => '2500']);

        $this->assertSame(2500.0, app(PricingService::class)->priceForPlan(Payment::PLAN_QUARTER));
    }

    public function test_employee_meal_price_is_read_from_settings(): void
    {
        app(SettingsService::class)->set('employee_meal_price', '75');

        $this->assertSame(75.0, app(PricingService::class)->priceForEmployeeMeal());
    }

    public function test_plan_end_dates_subtract_one_day(): void
    {
        $pricing = app(PricingService::class);

        $this->assertSame('2026-01-01', $pricing->computeEndDate('2026-01-01', Payment::PLAN_DAY)->toDateString());
        $this->assertSame('2026-01-31', $pricing->computeEndDate('2026-01-01', Payment::PLAN_MONTH)->toDateString());
        $this->assertSame('2026-03-31', $pricing->computeEndDate('2026-01-01', Payment::PLAN_QUARTER)->toDateString());
        $this->assertSame('2026-02-28', $pricing->computeEndDate('2026-02-01', Payment::PLAN_MONTH)->toDateString());
    }

    public function test_quarter_plan_covers_three_months(): void
    {
        $pricing = app(PricingService::class);

        $end = $pricing->computeEndDate('2026-01-15', Payment::PLAN_QUARTER);

        $this->assertSame(89, (int) \Illuminate\Support\Carbon::parse('2026-01-15')->diffInDays($end));
    }
}
