<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public const CACHE_KEY = 'zawia.settings';

    /** القيم الافتراضية للإعدادات */
    public const DEFAULTS = [
        'country' => 'الجمهورية الجزائرية الديمقراطية الشعبية',
        'ministry' => 'وزارة التكوين والتعليم المهنيين',
        'directorate' => '',
        'institution_name' => 'منصة الزاوية',
        'institution_code' => '',
        'institution_address' => '',
        'institution_phone' => '',
        'institution_email' => '',
        'institution_logo' => '',
        'currency' => 'دج',
        'meal_price' => '50',
        'days_per_month' => '22',
        'expiry_alert_days' => '7',
        'price_month' => '',
        'price_quarter' => '',
        'employee_meal_price' => '50',
        'employee_price_month' => '',
        'employee_price_quarter' => '',
        'print_margin_mm' => '8',
        'print_cards_per_page' => '10',
        'backup_retention' => '10',

        /* ---- تصميم بطاقة الطابعة ---- */
        // الترويسة
        'card_label' => 'بطاقة المطعم',
        'card_label_show' => '1',
        'card_label_size' => '7.6',
        'card_label_font' => 'reem',
        'card_label_color' => '#0c5f75',
        'card_inst_size' => '7.6',
        'card_official_size' => '6.4',
        'card_logo_show' => '1',
        'card_logo_size' => '9',
        // المتن والحقول
        'card_font_family' => 'cairo',
        'card_font_size' => '8.2',
        'card_fields_size' => '7.4',
        'card_field_label_w' => '15',
        'card_photo_show' => '1',
        'card_photo_w' => '17',
        'card_photo_h' => '21',
        'card_show_reg_no' => '1',
        'card_show_name' => '1',
        'card_show_birth' => '1',
        'card_show_specialty' => '1',
        'card_show_period' => '1',
        // الباركود ورمز QR
        'card_barcode_show' => '1',
        'card_barcode_h' => '7',
        'card_qr_show' => '1',
        'card_qr_size' => '22',
        // إطار البطاقة
        'card_w' => '96',
        'card_h' => '54',
        'card_radius' => '3',
        'card_border_w' => '0.3',
        'card_border_color' => '#94a3b8',
        'card_primary' => '#0e7490',
        'card_header_bg' => '#f8fafc',
        // التذييل والوجه الخلفي
        'card_foot_show' => '1',
        'card_foot_label' => 'رقم التعريف',
        'card_back_info_show' => '1',
        'card_back_note_show' => '1',
        'card_back_note' => 'الوجه الخلفي',
    ];

    /** الخطوط المتاحة لبطاقة الطابعة */
    public const CARD_FONTS = [
        'cairo' => ['label' => 'Cairo (كايرو)', 'google' => 'Cairo:wght@400;600;700;800', 'stack' => '"Cairo", "Segoe UI", Tahoma, sans-serif'],
        'reem' => ['label' => 'Reem Kufi (كوفي)', 'google' => 'Reem+Kufi:wght@500;600;700', 'stack' => '"Reem Kufi", "Cairo", sans-serif'],
        'amiri' => ['label' => 'Amiri (نسخ رسمي)', 'google' => 'Amiri:wght@400;700', 'stack' => '"Amiri", "Times New Roman", serif'],
        'tajawal' => ['label' => 'Tajawal (تجوال)', 'google' => 'Tajawal:wght@400;500;700', 'stack' => '"Tajawal", "Cairo", sans-serif'],
    ];

    /**
     * فئات البطاقات القابلة للتصميم المستقل.
     * "prefix" يُضاف إلى مفاتيح الإعدادات الخاصة بالفئة.
     */
    public const CARD_TYPES = [
        'trainee' => ['prefix' => '', 'label' => 'بطاقة المتربصين'],
        'employee' => ['prefix' => 'employee_', 'label' => 'بطاقة الموظفين'],
    ];

    /** قيم افتراضية خاصة بفئة معيّنة (الباقي يرث بطاقة المتربصين) */
    private const CARD_TYPE_OVERRIDES = [
        'employee_' => ['card_label' => 'بطاقة الموظف'],
    ];

    /** وصف مختصر لكل خط لأجل رابط خطوط جوجل */
    public static function cardFont(string $key, string $field = 'stack'): string
    {
        return self::CARD_FONTS[$key][$field] ?? self::CARD_FONTS['cairo'][$field];
    }

    /** جميع الإعدادات (مع التخزين المؤقت) */
    public function all(): array
    {
        // نخزّن مؤقتًا القيم المحفوظة فقط، ثم ندمجها مع الافتراضيات في كل نداء،
        // حتى لا تتسبب إضافة مفتاح افتراضي جديد في أي خطأ بسبب نسخة قديمة من التخزين المؤقت.
        $stored = Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::query()->pluck('value', 'key')->toArray();
        });

        $defaults = self::DEFAULTS;

        // كل فئة بطاقة ترث القيم الافتراضية للبطاقة الأساسية بمفتاح مسبوق.
        foreach (self::CARD_TYPES as $type) {
            $prefix = $type['prefix'];

            if ($prefix === '') {
                continue;
            }

            foreach (self::DEFAULTS as $key => $value) {
                if (str_starts_with($key, 'card_')) {
                    $defaults[$prefix.$key] = $value;
                }
            }

            foreach (self::CARD_TYPE_OVERRIDES[$prefix] ?? [] as $key => $value) {
                $defaults[$prefix.$key] = $value;
            }
        }

        return array_merge($defaults, $stored);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        if (array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '') {
            return $all[$key];
        }

        return $default ?? (self::DEFAULTS[$key] ?? null);
    }

    /** قيمة رقمية مع قيمة افتراضية */
    public function number(string $key, float $default = 0): float
    {
        $value = $this->get($key);

        return is_numeric($value) ? (float) $value : $default;
    }

    public function set(string $key, mixed $value): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value === null ? null : (string) $value, 'updated_at' => now()]
        );

        $this->flush();
    }

    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value === null ? null : (string) $value, 'updated_at' => now()]
            );
        }

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
