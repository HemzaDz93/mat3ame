<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Trainee;
use App\Services\AuditService;
use App\Services\FileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(
        private AuditService $audit,
        private FileService $files
    ) {
    }

    public function store(Request $request, Trainee $trainee): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:'.implode(',', array_keys(Document::TYPES))],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],
        ], [], ['type' => 'نوع الوثيقة', 'file' => 'الملف']);

        $file = $request->file('file');

        $path = $this->files->store(
            $file,
            'documents/'.$trainee->id,
            $data['type'].'-'.($trainee->electronic_reg_no ?: $trainee->reg_no ?: $trainee->id)
        );

        $document = $trainee->documents()->create([
            'type' => $data['type'],
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        $this->audit->log('document.uploaded', $document, 'رفع وثيقة للمتربص: '.$trainee->full_name.' ('.$document->typeLabel().')');

        return back()->with('success', 'تم رفع الوثيقة بنجاح.');
    }

    public function download(Trainee $trainee, Document $document): StreamedResponse|RedirectResponse
    {
        abort_unless($document->trainee_id === $trainee->id, 404);

        $response = $this->files->response($document->path, $document->original_name);

        abort_if($response === null, 404);

        $this->audit->log('document.downloaded', $document, 'تنزيل وثيقة: '.$document->typeLabel());

        return $response;
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->files->delete($document->path);

        $this->audit->log('document.deleted', $document, 'حذف وثيقة: '.$document->typeLabel());

        $document->delete();

        return back()->with('success', 'تم حذف الوثيقة.');
    }
}
