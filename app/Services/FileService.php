<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileService
{
    /** الصيغ المسموح بها للصور */
    public const IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * تخزين ملف مرفوع باسم آمن داخل القرص الخاص.
     * يُرجع المسار النسبي داخل القرص.
     */
    public function store(UploadedFile $file, string $directory, string $basename, array $allowedMimes = []): string
    {
        $mime = (string) $file->getMimeType();

        if ($allowedMimes && ! in_array($mime, $allowedMimes, true)) {
            throw new \InvalidArgumentException('نوع الملف غير مسموح.');
        }

        $extension = $this->safeExtension($file);
        $name = $this->sanitizeBase($basename).'-'.Str::random(8).'.'.$extension;

        Storage::disk('local')->putFileAs($directory, $file, $name);

        return trim($directory, '/').'/'.$name;
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    public function exists(?string $path): bool
    {
        if ($path === null || trim($path) === '') {
            return false;
        }

        return Storage::disk('local')->exists($path);
    }

    /** استجابة تنزيل/عرض آمنة لملف خاص */
    public function response(?string $path, ?string $downloadName = null): ?StreamedResponse
    {
        if (! $this->exists($path)) {
            return null;
        }

        return $downloadName
            ? Storage::disk('local')->download($path, $downloadName)
            : Storage::disk('local')->response($path);
    }

    public function size(?string $path): int
    {
        return $this->exists($path) ? (int) Storage::disk('local')->size($path) : 0;
    }

    /** إزالة الأصفار غير المرغوبة وتوحيد اسم الملف */
    private function sanitizeBase(string $value): string
    {
        $value = pathinfo($value, PATHINFO_FILENAME);
        $value = preg_replace('/[^A-Za-z0-9_\-]/u', '_', $value) ?? 'file';

        return Str::limit($value, 60, '');
    }

    private function safeExtension(UploadedFile $file): string
    {
        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension()));

        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: '';

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'xlsx', 'xlsm', 'csv', 'doc', 'docx'], true)
            ? $extension
            : 'bin';
    }
}
