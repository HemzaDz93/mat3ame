<?php

namespace App\Http\Controllers;

use App\Models\BackupLog;
use App\Services\AuditService;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct(
        private BackupService $backups,
        private AuditService $audit
    ) {
    }

    public function index(): View
    {
        return view('backups.index', [
            'backups' => BackupLog::query()->with('creator')->latest('id')->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        $log = $this->backups->create($request->user()->id, $request->input('note'));

        if ($log->status !== 'success') {
            $this->audit->log('backup.failed', $log, 'فشل إنشاء نسخة احتياطية: '.$log->note, null, null, 'failed');

            return back()->with('error', 'فشل إنشاء النسخة الاحتياطية: '.$log->note);
        }

        $this->audit->log('backup.created', $log, 'إنشاء نسخة احتياطية: '.$log->filename);

        return back()->with('success', 'تم إنشاء النسخة الاحتياطية بنجاح.');
    }

    public function download(BackupLog $backup): StreamedResponse
    {
        abort_unless($this->backups->exists($backup), 404);

        return response()->streamDownload(function () use ($backup) {
            $stream = Storage::disk('local')
                ->readStream(BackupService::DIR.'/'.basename($backup->filename));

            if ($stream !== null) {
                fpassthru($stream);
                fclose($stream);
            }
        }, $backup->filename, ['Content-Type' => 'application/sql']);
    }

    public function destroy(BackupLog $backup): RedirectResponse
    {
        $filename = $backup->filename;

        $this->backups->delete($backup);

        $this->audit->log('backup.deleted', null, 'حذف نسخة احتياطية: '.$filename);

        return back()->with('success', 'تم حذف النسخة الاحتياطية.');
    }
}
