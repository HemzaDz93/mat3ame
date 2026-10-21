<?php

namespace App\Http\Controllers;

use App\Exports\SpreadsheetExporter;
use App\Models\EmployeePayment;
use App\Models\Payment;
use App\Models\Trainee;
use App\Services\PaymentInsightsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(
        private SpreadsheetExporter $exporter,
        private PaymentInsightsService $insights
    ) {
    }

    /** تصدير قائمة المتربصين بنفس فلاتر شاشة القائمة */
    public function trainees(Request $request, string $format): StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'xlsx'], true), 404);

        $query = $request->boolean('trashed') ? Trainee::onlyTrashed() : Trainee::query();

        $trainees = $query
            ->search($request->input('q'))
            ->filter($request->only([
                'specialty', 'diploma', 'level', 'gender', 'training_mode',
                'start_from', 'start_to', 'end_from', 'end_to', 'status',
            ]))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->cursor();

        $rows = [[
            'م', 'رقم التسجيل', 'الرقم الإلكتروني', 'اللقب', 'الاسم', 'اللقب (فرنسي)', 'الاسم (فرنسي)',
            'الجنس', 'تاريخ الميلاد', 'مكان الميلاد', 'ولاية الميلاد', 'ولاية الإقامة', 'العنوان',
            'الهاتف', 'البريد الإلكتروني', 'اسم الأب', 'لقب الأم', 'اسم الأم', 'NIN',
            'الاختصاص', 'الشهادة', 'المستوى', 'نمط التكوين', 'تاريخ البداية', 'تاريخ النهاية',
            'الملف مقبول', 'مؤهل', 'المداولة', 'إلغاء الحساب', 'احتياجات خاصة', 'ملاحظات',
        ]];

        $index = 0;

        foreach ($trainees as $trainee) {
            $index++;

            $rows[] = [
                $index,
                $trainee->reg_no,
                $trainee->electronic_reg_no,
                $trainee->last_name,
                $trainee->first_name,
                $trainee->last_name_fr,
                $trainee->first_name_fr,
                $trainee->gender,
                $trainee->birth_date?->format('Y-m-d'),
                $trainee->birth_place,
                $trainee->birth_wilaya,
                $trainee->residence_wilaya,
                $trainee->address,
                $trainee->phone,
                $trainee->email,
                $trainee->father_name,
                $trainee->mother_last_name,
                $trainee->mother_name,
                $trainee->nin,
                $trainee->specialty,
                $trainee->diploma,
                $trainee->level,
                $trainee->training_mode,
                $trainee->start_date?->format('Y-m-d'),
                $trainee->end_date?->format('Y-m-d'),
                $trainee->file_accepted ? 'نعم' : 'لا',
                $trainee->eligible ? 'نعم' : 'لا',
                $trainee->deliberation_accepted ? 'نعم' : 'لا',
                $trainee->account_cancelled ? 'نعم' : 'لا',
                $trainee->special_needs,
                $trainee->notes,
            ];
        }

        $filename = 'trainees-'.now()->format('Ymd-His');

        return $format === 'csv'
            ? $this->exporter->csv($rows, $filename)
            : $this->exporter->xlsx($rows, $filename, 'المتربصون');
    }

    /** تصدير قائمة السدادات بنفس فلاتر شاشة القائمة */
    public function payments(Request $request, string $format): StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'xlsx'], true), 404);

        $payments = Payment::query()
            ->with(['trainee', 'requester', 'approver'])
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('plan'), fn ($q, $p) => $q->where('plan', $p))
            ->when($request->input('q'), function ($q, $term) {
                $term = trim($term);
                $q->whereHas('trainee', function ($w) use ($term) {
                    $w->where('last_name', 'like', "%{$term}%")
                        ->orWhere('first_name', 'like', "%{$term}%")
                        ->orWhere('reg_no', 'like', "%{$term}%")
                        ->orWhere('electronic_reg_no', 'like', "%{$term}%")
                        ->orWhere('nin', 'like', "%{$term}%");
                });
            })
            ->when($request->input('from'), fn ($q, $d) => $q->whereDate('start_date', '>=', $d))
            ->when($request->input('to'), fn ($q, $d) => $q->whereDate('end_date', '<=', $d))
            ->orderByDesc('id')
            ->cursor();

        $rows = [[
            'م', 'المتربص', 'رقم التسجيل', 'الرقم الإلكتروني', 'النوع', 'من تاريخ', 'إلى تاريخ',
            'المبلغ (دج)', 'الحالة', 'طالب السداد', 'المعتمد', 'تاريخ الإنشاء',
        ]];

        $index = 0;

        foreach ($payments as $payment) {
            $index++;

            $rows[] = [
                $index,
                $payment->trainee?->full_name,
                $payment->trainee?->reg_no,
                $payment->trainee?->electronic_reg_no,
                $payment->planLabel(),
                $payment->start_date?->format('Y-m-d'),
                $payment->end_date?->format('Y-m-d'),
                number_format((float) $payment->amount, 2, '.', ''),
                $payment->statusLabel(),
                $payment->requester?->name,
                $payment->approver?->name,
                $payment->created_at?->format('Y-m-d H:i'),
            ];
        }

        $filename = 'payments-'.now()->format('Ymd-His');

        return $format === 'csv'
            ? $this->exporter->csv($rows, $filename)
            : $this->exporter->xlsx($rows, $filename, 'السدادات');
    }

    /** تصدير قائمة سدادات الموظفين بنفس فلاتر شاشة القائمة */
    public function employeePayments(Request $request, string $format): StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'xlsx'], true), 404);

        $payments = EmployeePayment::query()
            ->with(['employee', 'requester', 'approver'])
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('plan'), fn ($q, $p) => $q->where('plan', $p))
            ->when($request->input('q'), function ($q, $term) {
                $term = trim($term);
                $q->whereHas('employee', function ($w) use ($term) {
                    $w->where('last_name', 'like', "%{$term}%")
                        ->orWhere('first_name', 'like', "%{$term}%")
                        ->orWhere('emp_no', 'like', "%{$term}%");
                });
            })
            ->when($request->input('from'), fn ($q, $d) => $q->whereDate('start_date', '>=', $d))
            ->when($request->input('to'), fn ($q, $d) => $q->whereDate('end_date', '<=', $d))
            ->orderByDesc('id')
            ->cursor();

        $rows = [[
            'م', 'الموظف', 'رقم الموظف', 'النوع', 'من تاريخ', 'إلى تاريخ',
            'المبلغ (دج)', 'الحالة', 'طالب السداد', 'المعتمد', 'تاريخ الإنشاء',
        ]];

        $index = 0;

        foreach ($payments as $payment) {
            $index++;

            $rows[] = [
                $index,
                $payment->employee?->full_name,
                $payment->employee?->emp_no,
                $payment->planLabel(),
                $payment->start_date?->format('Y-m-d'),
                $payment->end_date?->format('Y-m-d'),
                number_format((float) $payment->amount, 2, '.', ''),
                $payment->statusLabel(),
                $payment->requester?->name,
                $payment->approver?->name,
                $payment->created_at?->format('Y-m-d H:i'),
            ];
        }

        $filename = 'employee-payments-'.now()->format('Ymd-His');

        return $format === 'csv'
            ? $this->exporter->csv($rows, $filename)
            : $this->exporter->xlsx($rows, $filename, 'سدادات الموظفين');
    }

    /** تصدير قائمة المتأخرات (الديون) */
    public function paymentDebts(Request $request, string $format): StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'xlsx'], true), 404);

        $isEmployee = $request->input('tab') === 'employees';
        $debts = $isEmployee ? $this->insights->employeeDebts() : $this->insights->traineeDebts();

        $rows = [[
            'م', 'الاسم واللقب', $isEmployee ? 'رقم الموظف' : 'رقم التسجيل', 'نهاية آخر سداد',
            'أيام التأخر', 'الوجبات غير المسددة', 'المبلغ المستحق ('.currency().')',
        ]];

        $index = 0;

        foreach ($debts as $debt) {
            $index++;

            $rows[] = [
                $index,
                $debt['subject']->full_name,
                $isEmployee ? $debt['subject']->emp_no : $debt['subject']->reg_no,
                $debt['last_end']->format('Y-m-d'),
                $debt['days_overdue'],
                $debt['meals'],
                number_format($debt['amount'], 2, '.', ''),
            ];
        }

        $filename = 'payment-debts-'.now()->format('Ymd-His');

        return $format === 'csv'
            ? $this->exporter->csv($rows, $filename)
            : $this->exporter->xlsx($rows, $filename, 'المتأخرات');
    }
}
