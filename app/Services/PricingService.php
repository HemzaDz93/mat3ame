<?php

namespace App\Services;

use App\Models\Payment;
use Carbon\Carbon;

class PricingService
{
    public function __construct(private SettingsService $settings)
    {
    }

    /**
     * سعر الخطة:
     * - اليوم = meal_price
     * - الشهر = price_month إن كانت صالحة، وإلا meal_price × days_per_month
     * - الثلاثة أشهر = price_quarter إن كانت صالحة، وإلا meal_price × days_per_month × 3
     */
    public function priceForPlan(string $plan): float
    {
        $mealPrice = $this->settings->number('meal_price', 50);
        $daysPerMonth = $this->settings->number('days_per_month', 22);

        return match ($plan) {
            Payment::PLAN_DAY => $mealPrice,
            Payment::PLAN_MONTH => $this->resolvePrice('price_month', $mealPrice * $daysPerMonth),
            Payment::PLAN_QUARTER => $this->resolvePrice('price_quarter', $mealPrice * $daysPerMonth * 3),
            default => 0.0,
        };
    }

    private function resolvePrice(string $key, float $fallback): float
    {
        $value = $this->settings->get($key);

        if (is_numeric($value) && (float) $value > 0) {
            return (float) $value;
        }

        return $fallback;
    }

    /**
     * حساب تاريخ نهاية الخطة انطلاقًا من تاريخ البداية، بطرح يوم واحد من التاريخ الناتج.
     */
    public function computeEndDate(Carbon|string $startDate, string $plan): Carbon
    {
        $start = $startDate instanceof Carbon ? $startDate->copy() : Carbon::parse($startDate);
        $start = $start->startOfDay();

        return match ($plan) {
            Payment::PLAN_DAY => $start->copy(),
            Payment::PLAN_MONTH => $start->copy()->addMonthNoOverflow()->subDay(),
            Payment::PLAN_QUARTER => $start->copy()->addMonthsNoOverflow(3)->subDay(),
            default => $start->copy(),
        };
    }

    public function priceForEmployeeMeal(): float
    {
        return $this->settings->number('employee_meal_price', 50);
    }

    /**
     * سعر خطة سداد الموظف:
     * - اليوم = employee_meal_price
     * - الشهر = employee_price_month إن كانت صالحة، وإلا سعر وجبة الموظف × days_per_month
     * - الثلاثة أشهر = employee_price_quarter إن كانت صالحة، وإلا سعر وجبة الموظف × days_per_month × 3
     */
    public function priceForEmployeePlan(string $plan): float
    {
        $mealPrice = $this->settings->number('employee_meal_price', 50);
        $daysPerMonth = $this->settings->number('days_per_month', 22);

        return match ($plan) {
            Payment::PLAN_DAY => $mealPrice,
            Payment::PLAN_MONTH => $this->resolvePrice('employee_price_month', $mealPrice * $daysPerMonth),
            Payment::PLAN_QUARTER => $this->resolvePrice('employee_price_quarter', $mealPrice * $daysPerMonth * 3),
            default => 0.0,
        };
    }
}
