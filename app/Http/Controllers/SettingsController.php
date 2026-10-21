<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use App\Services\FileService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingsController extends Controller
{
    /** الحقول النصية القابلة للحفظ */
    private const TEXT_KEYS = [
        'country',
        'ministry',
        'directorate',
        'institution_name',
        'institution_code',
        'institution_address',
        'institution_phone',
        'institution_email',
        'currency',
    ];

    /** الحقول الرقمية القابلة للحفظ مع حدودها الدنيا */
    private const NUMBER_KEYS = [
        'meal_price' => 0,
        'employee_meal_price' => 0,
        'days_per_month' => 1,
        'expiry_alert_days' => 1,
        'print_margin_mm' => 0,
        'print_cards_per_page' => 1,
        'backup_retention' => 1,
    ];

    /** حقول الأسعار الاختيارية (قد تكون فارغة) */
    private const OPTIONAL_NUMBER_KEYS = ['price_month', 'price_quarter', 'employee_price_month', 'employee_price_quarter'];

    /** حقول بطاقة الطابعة النصية */
    private const CARD_TEXT_KEYS = ['card_label', 'card_foot_label', 'card_back_note'];

    /** حقول بطاقة الطابعة اللونية */
    private const CARD_COLOR_KEYS = ['card_label_color', 'card_border_color', 'card_primary', 'card_header_bg'];

    /** حقول بطاقة الطابعة الرقمية مع حدودها الدنيا */
    private const CARD_NUMBER_KEYS = [
        'card_label_size' => 1,
        'card_inst_size' => 1,
        'card_official_size' => 1,
        'card_logo_size' => 0,
        'card_font_size' => 1,
        'card_fields_size' => 1,
        'card_field_label_w' => 0,
        'card_photo_w' => 0,
        'card_photo_h' => 0,
        'card_barcode_h' => 0,
        'card_qr_size' => 0,
        'card_w' => 20,
        'card_h' => 20,
        'card_radius' => 0,
        'card_border_w' => 0,
    ];

    /** مفاتيح تشغيل/إيقاف عناصر البطاقة */
    private const CARD_BOOL_KEYS = [
        'card_label_show',
        'card_logo_show',
        'card_photo_show',
        'card_show_reg_no',
        'card_show_name',
        'card_show_birth',
        'card_show_specialty',
        'card_show_period',
        'card_barcode_show',
        'card_qr_show',
        'card_foot_show',
        'card_back_info_show',
        'card_back_note_show',
    ];

    /** حد أقصى موحّد لحجم الخط أو البعد بالملّيمتر */
    private const CARD_MAX_SIZE = 200;

    private const CARD_FONT_KEYS = ['card_label_font', 'card_font_family'];

    public function __construct(
        private SettingsService $settings,
        private FileService $files,
        private AuditService $audit
    ) {
    }

    public function edit(): View
    {
        return view('settings.edit', [
            'values' => $this->settings->all(),
            'cardFonts' => SettingsService::CARD_FONTS,
            'cardTypes' => SettingsService::CARD_TYPES,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [
            'country' => ['nullable', 'string', 'max:120'],
            'ministry' => ['nullable', 'string', 'max:120'],
            'directorate' => ['nullable', 'string', 'max:120'],
            'institution_name' => ['required', 'string', 'max:120'],
            'institution_code' => ['nullable', 'string', 'max:60'],
            'institution_address' => ['nullable', 'string', 'max:180'],
            'institution_phone' => ['nullable', 'string', 'max:40'],
            'institution_email' => ['nullable', 'email', 'max:120'],
            'currency' => ['nullable', 'string', 'max:12'],
            'meal_price' => ['required', 'numeric', 'min:0'],
            'employee_meal_price' => ['required', 'numeric', 'min:0'],
            'days_per_month' => ['required', 'integer', 'min:1', 'max:31'],
            'expiry_alert_days' => ['required', 'integer', 'min:1', 'max:90'],
            'price_month' => ['nullable', 'numeric', 'min:0'],
            'price_quarter' => ['nullable', 'numeric', 'min:0'],
            'employee_price_month' => ['nullable', 'numeric', 'min:0'],
            'employee_price_quarter' => ['nullable', 'numeric', 'min:0'],
            'print_margin_mm' => ['required', 'integer', 'min:0', 'max:40'],
            'print_cards_per_page' => ['required', 'integer', 'min:1', 'max:20'],
            'backup_retention' => ['required', 'integer', 'min:1', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
        ];

        /* ---- بطاقة الطابعة (لكل فئة: المتربصون والموظفون) ---- */
        foreach (SettingsService::CARD_TYPES as $type) {
            $p = $type['prefix'];

            foreach (self::CARD_TEXT_KEYS as $key) {
                $rules[$p.$key] = ['nullable', 'string', 'max:60'];
            }

            foreach (self::CARD_FONT_KEYS as $key) {
                $rules[$p.$key] = ['required', Rule::in(array_keys(SettingsService::CARD_FONTS))];
            }

            foreach (self::CARD_COLOR_KEYS as $key) {
                $rules[$p.$key] = ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'];
            }

            foreach (self::CARD_NUMBER_KEYS as $key => $min) {
                $rules[$p.$key] = ['required', 'numeric', 'min:'.$min, 'max:'.self::CARD_MAX_SIZE];
            }

            foreach (self::CARD_BOOL_KEYS as $key) {
                $rules[$p.$key] = ['nullable', 'boolean'];
            }
        }

        $request->validate($rules, [
            'card_label_font.in' => 'الخط المختار غير مدعوم.',
            'card_font_family.in' => 'الخط المختار غير مدعوم.',
            'employee_card_label_font.in' => 'الخط المختار غير مدعوم.',
            'employee_card_font_family.in' => 'الخط المختار غير مدعوم.',
        ], [
            'institution_name' => 'اسم المؤسسة',
            'meal_price' => 'سعر الوجبة',
            'card_label' => 'تسمية البطاقة',
            'card_label_font' => 'خط التسمية',
            'card_font_family' => 'خط البطاقة',
        ]);

        $values = [];

        foreach (self::TEXT_KEYS as $key) {
            $values[$key] = (string) $request->input($key, '');
        }

        foreach (self::NUMBER_KEYS as $key => $min) {
            $values[$key] = (string) max($min, (float) $request->input($key));
        }

        foreach (self::OPTIONAL_NUMBER_KEYS as $key) {
            $value = $request->input($key);
            $values[$key] = ($value === null || $value === '') ? '' : (string) $value;
        }

        /* ---- بطاقة الطابعة (لكل فئة) ---- */
        foreach (SettingsService::CARD_TYPES as $type) {
            $p = $type['prefix'];

            foreach (self::CARD_TEXT_KEYS as $key) {
                $values[$p.$key] = (string) $request->input($p.$key, '');
            }

            foreach (self::CARD_COLOR_KEYS as $key) {
                $values[$p.$key] = (string) $request->input($p.$key, '');
            }

            foreach (self::CARD_FONT_KEYS as $key) {
                $values[$p.$key] = (string) $request->input($p.$key);
            }

            foreach (self::CARD_NUMBER_KEYS as $key => $min) {
                $values[$p.$key] = (string) max($min, (float) $request->input($p.$key));
            }

            foreach (self::CARD_BOOL_KEYS as $key) {
                $values[$p.$key] = $request->boolean($p.$key) ? '1' : '0';
            }
        }

        if ($request->hasFile('logo')) {
            $old = $this->settings->get('institution_logo');

            $path = $this->files->store($request->file('logo'), 'settings', 'logo', FileService::IMAGE_MIMES);

            $values['institution_logo'] = $path;

            if ($old && $old !== $path) {
                $this->files->delete($old);
            }
        } elseif ($request->boolean('remove_logo')) {
            $this->files->delete($this->settings->get('institution_logo'));
            $values['institution_logo'] = '';
        }

        $this->settings->setMany($values);

        $this->audit->log('settings.updated', null, 'تحديث إعدادات المؤسسة');

        return redirect()->route('settings.edit')->with('success', 'تم حفظ الإعدادات بنجاح.');
    }

    /** عرض شعار المؤسسة المخزّن */
    public function logo(): StreamedResponse
    {
        $response = $this->files->response($this->settings->get('institution_logo'));

        abort_if($response === null, 404);

        return $response;
    }
}
