<?php

namespace App\Http\Controllers;

use App\Imports\TraineeImporter;
use App\Models\ImportLog;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ImportController extends Controller
{
    /** مجلد ملفات الانتظار المؤقتة داخل القرص الخاص */
    private const TEMP_DIR = 'imports/pending';

    public function __construct(
        private TraineeImporter $importer,
        private AuditService $audit,
        private NotificationService $notifications
    ) {
    }

    public function form(): View
    {
        return view('import.form', [
            'result' => session('import_result'),
            'history' => ImportLog::query()->with('creator')->latest('id')->limit(10)->get(),
        ]);
    }

    /** رفع الملف وتحليله دون كتابة أي بيانات */
    public function preview(Request $request): View|RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xlsm,csv', 'max:20480'],
            'skip_first_row' => ['nullable', 'boolean'],
        ], [
            'file.required' => 'يرجى اختيار ملف Excel للاستيراد.',
            'file.mimes' => 'الصيغ المسموح بها: xlsx أو xlsm أو csv.',
            'file.max' => 'حجم الملف يتجاوز الحد المسموح (20 ميغابايت).',
        ], ['file' => 'الملف']);

        $file = $request->file('file');
        $skipFirstRow = $request->boolean('skip_first_row', true);
        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension()));

        $name = Str::random(40).'.'.$extension;
        Storage::disk('local')->putFileAs(self::TEMP_DIR, $file, $name);

        $relative = self::TEMP_DIR.'/'.$name;

        try {
            $analysis = $this->importer->analyse(Storage::disk('local')->path($relative), $skipFirstRow);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($relative);

            return back()->with('error', $e->getMessage())->withInput();
        }

        return view('import.preview', [
            'token' => $name,
            'filename' => $file->getClientOriginalName(),
            'skipFirstRow' => $skipFirstRow,
            'counts' => $analysis['counts'],
            'rows' => $analysis['rows'],
        ]);
    }

    /** تنفيذ الاستيراد الفعلي للملف الذي تمت معاينته */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'max:80'],
            'filename' => ['nullable', 'string', 'max:255'],
            'skip_first_row' => ['nullable', 'boolean'],
        ]);

        $token = basename((string) $request->input('token'));

        if (! preg_match('/^[A-Za-z0-9]+\.(xlsx|xlsm|csv)$/', $token)) {
            return redirect()->route('import.form')->with('error', 'مرجع الملف غير صالح، يرجى إعادة الرفع.');
        }

        $relative = self::TEMP_DIR.'/'.$token;

        if (! Storage::disk('local')->exists($relative)) {
            return redirect()->route('import.form')->with('error', 'انتهت صلاحية ملف المعاينة، يرجى إعادة رفعه.');
        }

        $filename = $request->input('filename') ?: $token;

        try {
            $result = $this->importer->import(
                Storage::disk('local')->path($relative),
                $request->user()->id,
                $request->boolean('skip_first_row', true)
            );
        } catch (\Throwable $e) {
            $this->audit->log('import.failed', null, 'فشل استيراد الملف: '.$filename, null, null, 'failed');

            return redirect()->route('import.form')->with('error', 'فشل الاستيراد: '.$e->getMessage());
        }

        Storage::disk('local')->delete($relative);

        $counts = $result['counts'];

        $log = ImportLog::create([
            'filename' => $filename,
            'sheet' => TraineeImporter::PREFERRED_SHEET,
            'total_rows' => $counts['total'],
            'accepted' => $counts['created'] + $counts['restored'],
            'updated' => $counts['updated'],
            'rejected' => $counts['rejected'],
            'skipped' => $counts['skipped'],
            'report' => ['counts' => $counts, 'rows' => array_slice($result['rows'], 0, 500)],
            'status' => 'success',
            'created_by' => $request->user()->id,
        ]);

        $this->audit->log('import.completed', $log, 'استيراد ملف: '.$filename, null, $counts);

        if (($counts['created'] + $counts['updated'] + $counts['restored']) > 0) {
            $this->notifications->notify(
                ['admin', 'staff'],
                'import',
                'اكتمل استيراد ملف المتربصين',
                sprintf(
                    'الملف %s: إنشاء %d، تحديث %d، استعادة %d، رفض %d.',
                    $filename,
                    $counts['created'],
                    $counts['updated'],
                    $counts['restored'],
                    $counts['rejected']
                ),
                'success',
                ['import_log_id' => $log->id]
            );
        }

        return redirect()->route('import.form')
            ->with('success', 'تم الاستيراد بنجاح.')
            ->with('import_result', [
                'filename' => $filename,
                'counts' => $counts,
                'rows' => array_slice($result['rows'], 0, 200),
            ]);
    }
}
