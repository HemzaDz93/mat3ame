<?php

namespace App\Imports;

use App\Models\Document;
use App\Models\Trainee;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;

/**
 * استيراد المتربصين من ملفات Excel بصيغة MINASA.
 *
 * المفتاح الأساسي للاستيراد هو رقم التسجيل `reg_no` (العمود 10 في ورقة MINASA).
 * خريطة الأعمدة تعتمد الترقيم من الصفر وفق مواصفات المشروع، ويجب التحقق منها
 * على ملف Excel حقيقي قبل الاعتماد النهائي (عدّل الثابت COLUMNS عند الحاجة).
 */
class TraineeImporter
{
    public const PREFERRED_SHEET = 'MINASA';

    /** الحد الأقصى لعدد الصفوف المقروءة */
    private const MAX_ROWS = 20000;

    /** خريطة الأعمدة (ترقيم من الصفر) */
    public const COLUMNS = [
        'eligible' => 1,
        'file_accepted' => 2,
        'deliberation_accepted' => 3,
        'account_cancelled' => 4,
        'notes' => 5,
        'electronic_reg_no' => 8,
        'reg_no' => 9,
        'source_photo' => 10,
        'registered_at' => 24,
        'last_name' => 25,
        'first_name' => 26,
        'last_name_fr' => 27,
        'first_name_fr' => 28,
        'birth_date' => 29,
        'birth_place' => 30,
        'birth_wilaya' => 31,
        'residence_wilaya' => 32,
        'gender' => 33,
        'address' => 35,
        'email' => 36,
        'phone' => 37,
        'father_name' => 38,
        'mother_last_name' => 39,
        'mother_name' => 40,
        'nin' => 41,
        'special_needs' => 44,
        'specialty' => 46,
        'diploma' => 47,
        'level' => 48,
        'training_mode' => 49,
        'start_date' => 51,
        'end_date' => 52,
    ];

    /** خريطة أعمدة الوثائق (ترقيم من الصفر) */
    public const DOCUMENT_COLUMNS = [
        11 => 'medical',
        12 => 'school',
        13 => 'national_education',
        14 => 'apprenticeship_contract',
        15 => 'training_experience',
        16 => 'birth_certificate',
        21 => 'residence_grant',
        22 => 'unemployment_orientation',
    ];

    /** تحليل الملف دون أي كتابة في قاعدة البيانات */
    public function analyse(string $path, bool $skipFirstRow = true): array
    {
        return $this->classify($this->parse($path, $skipFirstRow), false, null);
    }

    /** تنفيذ الاستيراد الفعلي داخل معاملة قاعدة بيانات */
    public function import(string $path, ?int $userId = null, bool $skipFirstRow = true): array
    {
        $parsed = $this->parse($path, $skipFirstRow);

        return DB::transaction(fn () => $this->classify($parsed, true, $userId));
    }

    /* ---------------------------------------------------------------
     |  القراءة
     | --------------------------------------------------------------- */

    /** @return array<int, array{row:int, data:array<string, mixed>, documents:array<int, string>}> */
    private function parse(string $path, bool $skipFirstRow): array
    {
        try {
            $reader = IOFactory::createReaderForFile($path);
        } catch (\Throwable $e) {
            throw new RuntimeException('تعذّر قراءة الملف. تأكد من أنه ملف Excel صالح (xlsx أو xlsm).');
        }

        $reader->setReadDataOnly(true);

        $names = $reader->listWorksheetNames($path);
        $sheetName = in_array(self::PREFERRED_SHEET, $names, true) ? self::PREFERRED_SHEET : ($names[0] ?? null);

        if ($sheetName === null) {
            throw new RuntimeException('الملف لا يحتوي على أي ورقة عمل.');
        }

        $reader->setLoadSheetsOnly([$sheetName]);

        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getSheetByName($sheetName);

        if ($sheet === null) {
            throw new RuntimeException('تعذّر الوصول إلى ورقة العمل: '.$sheetName);
        }

        $parsed = [];
        $maxColumn = max(self::COLUMNS) + 1;
        $number = 0;

        foreach ($sheet->getRowIterator() as $row) {
            $number++;

            if ($number > self::MAX_ROWS) {
                break;
            }

            if ($skipFirstRow && $number === 1) {
                continue;
            }

            $values = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                $index = $cell->getColumn() !== '' ? \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($cell->getColumn()) - 1 : count($values);
                $values[$index] = $this->cellValue($cell);
            }

            $hasData = false;

            for ($i = 0; $i < $maxColumn; $i++) {
                if (($values[$i] ?? null) !== null && $values[$i] !== '') {
                    $hasData = true;
                    break;
                }
            }

            if (! $hasData) {
                continue;
            }

            $parsed[] = $this->mapRow($number, $values);
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $parsed;
    }

    /** قراءة قيمة الخلية: القيمة المحسوبة المخزنة وليس الصيغة */
    private function cellValue(\PhpOffice\PhpSpreadsheet\Cell\Cell $cell): mixed
    {
        if ($cell->getDataType() === DataType::TYPE_FORMULA) {
            $calculated = $cell->getOldCalculatedValue();

            return $calculated === null ? null : $calculated;
        }

        $value = $cell->getValue();

        if (is_string($value)) {
            $value = trim($value);
        }

        return $value === '' ? null : $value;
    }

    /** @return array{row:int, data:array<string, mixed>, documents:array<int, string>} */
    private function mapRow(int $number, array $values): array
    {
        $get = fn (string $key) => $values[self::COLUMNS[$key]] ?? null;

        $documents = [];

        foreach (self::DOCUMENT_COLUMNS as $column => $type) {
            $value = $this->text($values[$column] ?? null);

            if ($value !== null) {
                $documents[$column] = $value;
            }
        }

        $gender = mb_strtoupper((string) $this->text($get('gender')));

        return [
            'row' => $number,
            'data' => [
                'electronic_reg_no' => $this->text($get('electronic_reg_no'), true),
                'reg_no' => $this->regNo($get('reg_no')),
                'last_name' => $this->text($get('last_name')),
                'first_name' => $this->text($get('first_name')),
                'last_name_fr' => $this->text($get('last_name_fr')),
                'first_name_fr' => $this->text($get('first_name_fr')),
                'gender' => in_array($gender, ['M', 'F'], true) ? $gender : (in_array($gender, ['ذكر', 'ذ'], true) ? 'M' : (in_array($gender, ['أنثى'], true) ? 'F' : null)),
                'birth_date' => $this->date($get('birth_date')),
                'birth_place' => $this->text($get('birth_place')),
                'birth_wilaya' => $this->text($get('birth_wilaya')),
                'residence_wilaya' => $this->text($get('residence_wilaya')),
                'address' => $this->text($get('address')),
                'email' => $this->text($get('email')),
                'phone' => $this->phone($get('phone')),
                'father_name' => $this->text($get('father_name')),
                'mother_last_name' => $this->text($get('mother_last_name')),
                'mother_name' => $this->text($get('mother_name')),
                'nin' => $this->text($get('nin'), true),
                'specialty' => $this->text($get('specialty')),
                'diploma' => $this->text($get('diploma')),
                'level' => $this->text($get('level')),
                'training_mode' => $this->text($get('training_mode')),
                'start_date' => $this->date($get('start_date')),
                'end_date' => $this->date($get('end_date')),
                'eligible' => $this->flag($get('eligible')),
                'file_accepted' => $this->flag($get('file_accepted')),
                'deliberation_accepted' => $this->flag($get('deliberation_accepted')),
                'account_cancelled' => $this->flag($get('account_cancelled')),
                'special_needs' => $this->text($get('special_needs')),
                'notes' => $this->text($get('notes')),
                'source_photo' => $this->text($get('source_photo')),
                'registered_at' => $this->datetime($get('registered_at')),
            ],
            'documents' => $documents,
        ];
    }

    /* ---------------------------------------------------------------
     |  التصنيف والكتابة
     | --------------------------------------------------------------- */

    /** @param array<int, array{row:int, data:array<string, mixed>, documents:array<int, string>}> $parsed */
    private function classify(array $parsed, bool $persist, ?int $userId): array
    {
        $seen = [];
        $rows = [];
        $counts = [
            'total' => count($parsed),
            'created' => 0,
            'updated' => 0,
            'restored' => 0,
            'rejected' => 0,
            'skipped' => 0,
        ];

        foreach ($parsed as $item) {
            $data = $item['data'];
            $reasons = [];
            $action = 'reject';

            $identity = $data['reg_no'];

            if ($data['last_name'] === null || $data['first_name'] === null) {
                $reasons[] = 'الاسم أو اللقب مفقود';
            }

            if ($identity === null) {
                $reasons[] = 'رقم التسجيل مفقود';
            } elseif (isset($seen[$identity])) {
                $reasons[] = 'تكرار رقم التسجيل في الملف (السطر '.$seen[$identity].')';
            }

            $accepted = (bool) $data['file_accepted'] && (bool) $data['eligible'];

            $existing = null;

            if ($reasons === [] && $identity !== null) {
                $existing = Trainee::withTrashed()->where('reg_no', $identity)->first();
            }

            if ($reasons === []) {
                if ($existing && $existing->trashed()) {
                    if ($accepted) {
                        $action = 'restore';
                    } else {
                        $action = 'skip';
                        $reasons[] = 'سجل محذوف والصف غير مقبول: لم يُستعد';
                    }
                } elseif ($existing) {
                    $action = 'update';
                } elseif ($accepted) {
                    $action = 'create';
                } else {
                    $reasons[] = 'صف غير مقبول: الملف غير مقبول أو المتربص غير مؤهل';
                }
            }

            if ($identity !== null) {
                $seen[$identity] = $item['row'];
            }

            if ($persist && in_array($action, ['create', 'update', 'restore'], true)) {
                $trainee = $this->write($data, $existing, $userId);
                $this->writeDocuments($trainee, $item['documents'], $userId);
            }

            match ($action) {
                'create' => $counts['created']++,
                'update' => $counts['updated']++,
                'restore' => $counts['restored']++,
                'skip' => $counts['skipped']++,
                default => $counts['rejected']++,
            };

            $rows[] = [
                'row' => $item['row'],
                'action' => $action,
                'name' => trim((string) $data['last_name'].' '.(string) $data['first_name']),
                'reg_no' => $identity,
                'reasons' => $reasons,
            ];
        }

        return ['rows' => $rows, 'counts' => $counts];
    }

    private function write(array $data, ?Trainee $existing, ?int $userId): Trainee
    {
        $values = $data;
        $values['registered_at'] = $values['registered_at'] ?: now();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->fill($values)->save();

            return $existing;
        }

        return Trainee::create($values);
    }

    /** @param array<int, string> $documents */
    private function writeDocuments(Trainee $trainee, array $documents, ?int $userId): void
    {
        foreach ($documents as $column => $path) {
            $type = self::DOCUMENT_COLUMNS[$column] ?? null;

            if ($type === null) {
                continue;
            }

            Document::query()->updateOrCreate(
                ['trainee_id' => $trainee->id, 'type' => $type],
                [
                    'path' => $path,
                    'original_name' => basename(str_replace('\\', '/', $path)),
                    'mime_type' => null,
                    'file_size' => 0,
                    'uploaded_by' => $userId,
                ]
            );
        }
    }

    /* ---------------------------------------------------------------
     |  تطبيع القيم
     | --------------------------------------------------------------- */

    private function text(mixed $value, bool $keepAsText = false): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_float($value) || is_int($value)) {
            $value = is_float($value) && floor($value) === $value
                ? number_format($value, 0, '.', '')
                : (string) $value;
        }

        $text = trim($this->normalizeDigits((string) $value));
        $text = preg_replace('/\.0+$/', '', $text) ?? $text;
        $text = trim($text);

        return $text === '' ? null : ($keepAsText ? $text : $text);
    }

    /** أرقام التسجيل: إزالة .0 وقص إلى 8 خانات */
    private function regNo(mixed $value): ?string
    {
        $text = $this->text($value);

        if ($text === null) {
            return null;
        }

        $text = preg_replace('/[^0-9A-Za-z\-\/]/', '', $text) ?? $text;

        return $text === '' ? null : mb_substr($text, 0, 8);
    }

    /** الهاتف: إضافة صفر بادئ عندما يكون الرقم من 9 خانات */
    private function phone(mixed $value): ?string
    {
        $text = $this->text($value);

        if ($text === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $text) ?? '';

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 9) {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    private function flag(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null) {
            return false;
        }

        if (is_numeric($value)) {
            return ((float) $value) >= 1;
        }

        $text = mb_strtolower(trim($this->normalizeDigits((string) $value)));

        return in_array($text, ['1', 'true', 'yes', 'y', 'o', 'x', '✓', 'نعم', 'صحيح', 'مقبول'], true);
    }

    private function date(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value) && (float) $value > 1000) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        $text = trim($this->normalizeDigits((string) $value));

        if ($text === '') {
            return null;
        }

        // إزالة جزء الوقت الزائد إن وُجد (مثال: "18/10/1995 00:00:00" أو "1995-10-18T00:00:00").
        if (preg_match('/^(.*?)[\sT]\d{1,2}:\d{2}(?::\d{2})?$/', $text, $matches)) {
            $text = trim($matches[1]);
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'd.m.Y', 'm/d/Y'] as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $text);
            } catch (\Throwable) {
                // Carbon 3 يطلق استثناءً عند عدم مطابقة الصيغة، فنجرّب الصيغة التالية.
                continue;
            }

            if ($parsed !== false && $parsed->format($format) === $text) {
                return $parsed->format('Y-m-d');
            }
        }

        try {
            return Carbon::parse($text)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function datetime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value) && (float) $value > 1000) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d H:i:s');
            } catch (\Throwable) {
                return null;
            }
        }

        $text = trim($this->normalizeDigits((string) $value));

        try {
            return Carbon::parse($text)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    /** تحويل الأرقام العربية والفارسية إلى أرقام لاتينية */
    private function normalizeDigits(string $value): string
    {
        return str_replace(
            ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $value
        );
    }
}
