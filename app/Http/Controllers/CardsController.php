<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Trainee;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * اختيار وطباعة البطاقات التعريفية للمتربصين والموظفين.
 * التخطيط: A4 عمودي، بطاقتان في الصف، خمسة صفوف (10 بطاقات في الصفحة).
 */
class CardsController extends Controller
{
    /** الحد الأقصى للبطاقات في عملية طباعة واحدة */
    private const MAX_CARDS = 200;

    public function __construct(private AuditService $audit)
    {
    }

    /** اختيار بطاقات المتربصين */
    public function index(Request $request): View
    {
        $trainees = Trainee::query()
            ->search($request->input('q'))
            ->filter($request->only(['specialty', 'diploma', 'level', 'gender', 'training_mode', 'status']))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(25)
            ->withQueryString();

        return view('cards.index', [
            'trainees' => $trainees,
            'specialties' => $this->distinctTrainee('specialty'),
            'diplomas' => $this->distinctTrainee('diploma'),
            'levels' => $this->distinctTrainee('level'),
        ]);
    }

    /** معاينة وطباعة بطاقات المتربصين المحددين */
    public function print(Request $request): View|RedirectResponse
    {
        $ids = $this->ids($request);

        if (empty($ids)) {
            return back()->with('error', 'لم يتم تحديد أي متربص لطباعة البطاقات.');
        }

        $ids = array_slice($ids, 0, self::MAX_CARDS);

        $trainees = Trainee::query()
            ->whereIn('id', $ids)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        if ($trainees->isEmpty()) {
            return back()->with('error', 'لا توجد سجلات مطابقة للمحدد.');
        }

        $this->audit->log('cards.printed', null, 'طباعة بطاقات متربصين: '.$trainees->count().' بطاقة');

        return view('cards.print', [
            'trainees' => $trainees,
            'pages' => $trainees->chunk(10),
            'cardPrefix' => '',
        ]);
    }

    /** اختيار بطاقات الموظفين */
    public function employees(Request $request): View
    {
        $employees = Employee::query()
            ->search($request->input('q'))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(25)
            ->withQueryString();

        return view('employees.cards', ['employees' => $employees]);
    }

    /** معاينة وطباعة بطاقات الموظفين المحددين */
    public function employeesPrint(Request $request): View|RedirectResponse
    {
        $ids = $this->ids($request);

        if (empty($ids)) {
            return back()->with('error', 'لم يتم تحديد أي موظف لطباعة البطاقات.');
        }

        $ids = array_slice($ids, 0, self::MAX_CARDS);

        $employees = Employee::query()
            ->whereIn('id', $ids)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        if ($employees->isEmpty()) {
            return back()->with('error', 'لا توجد سجلات مطابقة للمحدد.');
        }

        $this->audit->log('cards.printed', null, 'طباعة بطاقات موظفين: '.$employees->count().' بطاقة');

        return view('employees.cards-print', [
            'employees' => $employees,
            'pages' => $employees->chunk(10),
            'cardPrefix' => 'employee_',
        ]);
    }

    /** @return \Illuminate\Support\Collection<int, string> */
    private function distinctTrainee(string $column): \Illuminate\Support\Collection
    {
        return Trainee::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column);
    }

    /** @return array<int, int> */
    private function ids(Request $request): array
    {
        $ids = $request->input('ids', []);

        return collect(is_array($ids) ? $ids : explode(',', (string) $ids))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
