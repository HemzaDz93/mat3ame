<?php

namespace App\Http\Controllers;

use App\Exports\SpreadsheetExporter;
use App\Models\Trainee;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * القوائم الاسمية المجمعة حسب الاختصاص.
 */
class RosterController extends Controller
{
    public function __construct(private SpreadsheetExporter $exporter)
    {
    }

    public function index(Request $request): View
    {
        $data = $this->data($request);

        return view('roster.index', [
            'rows' => $data['rows'],
            'total' => $data['total'],
            'specialties' => $this->distinct('specialty'),
            'diplomas' => $this->distinct('diploma'),
            'levels' => $this->distinct('level'),
            'modes' => $this->distinct('training_mode'),
            'generatedAt' => now(),
        ]);
    }

    public function export(Request $request, string $format)
    {
        $data = $this->data($request);

        $rows = [
            [setting('country', '')],
            [setting('ministry', '')],
            [setting('directorate', '')],
            [setting('institution_name', '')],
            [],
            ['تاريخ إنشاء التقرير', now()->format('Y-m-d H:i')],
            ['عدد السجلات', $data['total']],
        ];

        if ($data['filters']['specialty']) {
            $rows[] = ['الاختصاص', $data['filters']['specialty']];
        }

        $rows[] = [];
        $rows[] = ['م', 'رقم التسجيل', 'الرقم الإلكتروني', 'الاسم واللقب', 'الاختصاص', 'المستوى', 'الشهادة', 'نمط التكوين', 'الجنس'];

        $index = 1;

        foreach ($data['rows'] as $row) {
            $rows[] = [
                $index++,
                $row->reg_no ?: '—',
                $row->electronic_reg_no ?: '—',
                $row->full_name,
                $row->specialty ?: '—',
                $row->level ?: '—',
                $row->diploma ?: '—',
                $row->training_mode ?: '—',
                $row->gender_label,
            ];
        }

        $filename = 'roster-'.now()->format('Ymd-His');

        return $format === 'xlsx'
            ? $this->exporter->xlsx($rows, $filename, 'القائمة الاسمية')
            : $this->exporter->csv($rows, $filename);
    }

    /**
     * @return array{rows: Collection<int, Trainee>, total: int, filters: array<string, string>}
     */
    private function data(Request $request): array
    {
        $filters = [
            'specialty' => trim((string) $request->input('specialty', '')),
            'diploma' => trim((string) $request->input('diploma', '')),
            'level' => trim((string) $request->input('level', '')),
            'training_mode' => trim((string) $request->input('training_mode', '')),
        ];

        $sort = $request->input('sort') === 'reg_no' ? 'reg_no' : 'name';

        $query = Trainee::query()
            ->where('account_cancelled', false)
            ->when($filters['specialty'] !== '', fn ($q) => $q->where('specialty', 'like', '%'.$filters['specialty'].'%'))
            ->when($filters['diploma'] !== '', fn ($q) => $q->where('diploma', $filters['diploma']))
            ->when($filters['level'] !== '', fn ($q) => $q->where('level', $filters['level']))
            ->when($filters['training_mode'] !== '', fn ($q) => $q->where('training_mode', $filters['training_mode']));

        $sort === 'reg_no'
            ? $query->orderBy('reg_no')->orderBy('last_name')
            : $query->orderBy('last_name')->orderBy('first_name');

        $rows = $query->get();

        return [
            'rows' => $rows,
            'total' => $rows->count(),
            'filters' => $filters,
        ];
    }

    /** @return Collection<int, string> */
    private function distinct(string $column): Collection
    {
        return Trainee::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column);
    }
}
