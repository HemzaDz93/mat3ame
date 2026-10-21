<?php

namespace App\Http\Controllers;

use App\Http\Requests\MealForecastRequest;
use App\Models\Meal;
use App\Models\MealForecast;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MealForecastController extends Controller
{
    public function __construct(private AuditService $audit)
    {
    }

    public function index(Request $request): View
    {
        $forecasts = MealForecast::query()->orderByDesc('day')->paginate(30);

        $days = $forecasts->pluck('day')->map(fn ($day) => $day->toDateString())->values()->all();

        // الوجبات المنفّذة فعليًا لكل يوم معروض
        $served = Meal::query()
            ->selectRaw('DATE(day) as d, COUNT(*) as total')
            ->whereIn('day', $days)
            ->groupBy('d')
            ->pluck('total', 'd');

        return view('forecast.index', [
            'forecasts' => $forecasts,
            'served' => $served,
            'todayForecast' => MealForecast::forDay(now()->toDateString()),
            'tomorrowForecast' => MealForecast::forDay(now()->addDay()->toDateString()),
        ]);
    }

    public function store(MealForecastRequest $request): RedirectResponse
    {
        $day = $request->input('day');
        $count = (int) $request->input('expected_count');
        $note = $request->input('note');

        $forecast = MealForecast::forDay($day);

        $old = $forecast ? ['expected_count' => $forecast->expected_count, 'note' => $forecast->note] : null;

        if ($forecast) {
            $forecast->update([
                'expected_count' => $count,
                'note' => $note,
                'updated_by' => $request->user()->id,
            ]);
        } else {
            $forecast = MealForecast::create([
                'day' => $day,
                'expected_count' => $count,
                'note' => $note,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);
        }

        $this->audit->log(
            'meal_forecast.saved',
            $forecast,
            'إعلان العدد المتوقّع لوجبات المتربصين بتاريخ '.$forecast->day->toDateString().': '.$count,
            $old,
            ['expected_count' => $count, 'note' => $note]
        );

        return back()->with('success', 'تم حفظ العدد المتوقّع لوجبات يوم '.$forecast->day->toDateString().'.');
    }

    public function destroy(MealForecast $forecast): RedirectResponse
    {
        $day = $forecast->day->toDateString();

        $this->audit->log('meal_forecast.deleted', $forecast, 'حذف العدد المتوقّع بتاريخ '.$day);

        $forecast->delete();

        return back()->with('success', 'تم حذف توقّع يوم '.$day.'.');
    }
}
