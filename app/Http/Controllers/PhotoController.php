<?php

namespace App\Http\Controllers;

use App\Models\Trainee;
use App\Services\AuditService;
use App\Services\FileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use ZipArchive;

class PhotoController extends Controller
{
    /** مجلد الاستخراج المؤقت لأرشيف ZIP */
    private const TEMP_DIR = 'imports/photos-tmp';

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function __construct(
        private FileService $files,
        private AuditService $audit
    ) {
    }

    public function form(): View
    {
        return view('photos.form', [
            'result' => session('photo_result'),
        ]);
    }

    /** رفع جماعي للصور ومطابقة اسم الملف مع رقم التسجيل أو الرقم الإلكتروني أو NIN */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'archive' => ['nullable', 'file', 'mimes:zip', 'max:102400'],
        ], [
            'photos.*.image' => 'الملفات المرفوعة يجب أن تكون صورًا صالحة.',
            'photos.*.mimes' => 'صيغ الصور المسموح بها: jpg أو png أو webp.',
            'photos.*.max' => 'حجم الصورة يتجاوز الحد المسموح (8 ميغابايت).',
            'archive.mimes' => 'الأرشيف يجب أن يكون بصيغة zip.',
            'archive.max' => 'حجم الأرشيف يتجاوز الحد المسموح (100 ميغابايت).',
        ]);

        if (! $request->hasFile('photos') && ! $request->hasFile('archive')) {
            return back()->with('error', 'يرجى اختيار صور أو أرشيف ZIP واحد على الأقل.');
        }

        $matched = 0;
        $unmatched = [];
        $failed = [];

        foreach ($this->collect($request) as $item) {
            $key = $this->cleanKey($item['key']);

            if ($key === '') {
                $unmatched[] = $item['key'];

                continue;
            }

            $trainee = $this->resolve($key);

            if (! $trainee) {
                $unmatched[] = $item['key'];

                continue;
            }

            try {
                $old = $trainee->photo;

                $path = $this->files->store(
                    $item['file'],
                    'trainees/photos',
                    $trainee->identityCode(),
                    FileService::IMAGE_MIMES
                );

                $trainee->forceFill(['photo' => $path])->save();

                if ($old && $old !== $path) {
                    $this->files->delete($old);
                }

                $matched++;
            } catch (\Throwable $e) {
                $failed[] = $item['key'].' ('.$e->getMessage().')';
            }
        }

        Storage::disk('local')->deleteDirectory(self::TEMP_DIR);

        $this->audit->log('photos.bulk_uploaded', null, sprintf(
            'رفع صور جماعي: نجح %d، غير مطابق %d، فشل %d.',
            $matched,
            count($unmatched),
            count($failed)
        ));

        if ($matched === 0) {
            return back()->with('warning', 'لم يتم تحديث أي صورة. تأكد من تسمية الصور برقم التسجيل أو الرقم الإلكتروني أو NIN.')
                ->with('photo_result', compact('matched', 'unmatched', 'failed'));
        }

        return back()
            ->with('success', 'تم تحديث '.$matched.' صورة بنجاح.')
            ->with('photo_result', compact('matched', 'unmatched', 'failed'));
    }

    /**
     * تجميع الصور المرفوعة مباشرة من الأرشيف.
     *
     * @return array<int, array{key:string, file:UploadedFile}>
     */
    private function collect(Request $request): array
    {
        $items = [];

        foreach ($request->file('photos') ?? [] as $file) {
            $items[] = [
                'key' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'file' => $file,
            ];
        }

        if ($request->hasFile('archive')) {
            array_push($items, ...$this->extract($request->file('archive')));
        }

        return $items;
    }

    /** @return array<int, array{key:string, file:UploadedFile}> */
    private function extract(UploadedFile $archive): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new \RuntimeException('امتداد ZipArchive غير مفعّل في PHP.');
        }

        $zip = new ZipArchive();

        if ($zip->open($archive->getRealPath()) !== true) {
            return [];
        }

        $items = [];
        $target = Storage::disk('local')->path(self::TEMP_DIR);

        if (! is_dir($target)) {
            mkdir($target, 0775, true);
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);

            if ($entry === false || str_ends_with($entry, '/')) {
                continue;
            }

            $extension = strtolower(pathinfo($entry, PATHINFO_EXTENSION));

            if (! in_array($extension, self::IMAGE_EXTENSIONS, true)) {
                continue;
            }

            $contents = $zip->getFromIndex($i);

            if ($contents === false) {
                continue;
            }

            $tempName = Str::random(20).'.'.$extension;

            Storage::disk('local')->put(self::TEMP_DIR.'/'.$tempName, $contents);

            $items[] = [
                'key' => pathinfo($entry, PATHINFO_FILENAME),
                'file' => new UploadedFile(Storage::disk('local')->path(self::TEMP_DIR.'/'.$tempName), basename($entry), null, null, true),
            ];
        }

        $zip->close();

        return $items;
    }

    /** البحث عن المتربص بالرقم الإلكتروني أو رقم التسجيل أو NIN */
    private function resolve(string $key): ?Trainee
    {
        $candidates = array_unique([$key, ltrim($key, '0'), $this->digits($key)]);

        foreach ($candidates as $candidate) {
            if ($candidate === '') {
                continue;
            }

            $trainee = Trainee::query()
                ->where('electronic_reg_no', $candidate)
                ->orWhere('reg_no', $candidate)
                ->orWhere('nin', $candidate)
                ->first();

            if ($trainee) {
                return $trainee;
            }
        }

        return null;
    }

    private function cleanKey(string $value): string
    {
        return trim(preg_replace('/\s+/', '', $value) ?? $value);
    }

    private function digits(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }
}
