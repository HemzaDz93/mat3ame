@extends('layouts.app')

@section('title', 'إعدادات المؤسسة')
@section('page_title', 'إعدادات المؤسسة')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/card.css') }}">
    <style>
        .card-type-switch {
            display: inline-flex; gap: 6px; background: #eef2f6; border: 1px solid var(--c-border);
            border-radius: 10px; padding: 4px; margin-bottom: 16px;
        }
        .card-type-btn {
            display: inline-flex; align-items: center; gap: 7px; border: 0; background: transparent;
            padding: 8px 16px; border-radius: 7px; cursor: pointer; font: inherit; font-weight: 600; color: var(--c-muted);
        }
        .card-type-btn.is-active { background: #fff; color: var(--c-primary-700); box-shadow: var(--shadow-sm); }
        .card-type-panel.is-hidden { display: none; }
        .card-design-grid { display: grid; grid-template-columns: minmax(320px, 420px) minmax(0, 1fr); gap: 18px; align-items: start; }
        @media (max-width: 1100px) { .card-design-grid { grid-template-columns: 1fr; } }
        .card-preview-pane { position: sticky; top: 76px; }
        .card-preview-stage {
            background: #eef2f6; border: 1px solid var(--c-border); border-radius: 10px;
            padding: 14px; display: flex; flex-direction: column; gap: 14px; align-items: center; overflow: auto;
        }
        .card-preview-stage .stage-label { font-size: .78rem; color: var(--c-muted); align-self: stretch; }
        .design-sub {
            font-weight: 700; font-size: .86rem; color: var(--c-primary-700);
            margin: 2px 0 10px; padding-bottom: 5px; border-bottom: 1px dashed var(--c-border);
        }
        .design-block + .design-block { margin-top: 18px; }
        .checks { display: grid; grid-template-columns: repeat(auto-fill, minmax(178px, 1fr)); gap: 7px 14px; }
        input[type=color].form-control { padding: 2px; height: 38px; cursor: pointer; }
    </style>
@endpush

@section('content')
    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid-2">
            <div class="card">
                <div class="card-header"><h3><x-icon name="building" /> بيانات المؤسسة</h3></div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group col-span-full">
                            <label for="country">الجمهورية / الدولة</label>
                            <input id="country" name="country" class="form-control" value="{{ old('country', $values['country']) }}">
                        </div>
                        <div class="form-group col-span-full">
                            <label for="ministry">الوزارة</label>
                            <input id="ministry" name="ministry" class="form-control" value="{{ old('ministry', $values['ministry']) }}">
                        </div>
                        <div class="form-group col-span-full">
                            <label for="directorate">المديرية</label>
                            <input id="directorate" name="directorate" class="form-control" value="{{ old('directorate', $values['directorate']) }}">
                        </div>
                        <div class="form-group col-span-full">
                            <label for="institution_name">اسم المؤسسة <span class="text-danger">*</span></label>
                            <input id="institution_name" name="institution_name" class="form-control @error('institution_name') is-invalid @enderror" value="{{ old('institution_name', $values['institution_name']) }}" required>
                            @error('institution_name')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="institution_code">رمز المؤسسة</label>
                            <input id="institution_code" name="institution_code" class="form-control" value="{{ old('institution_code', $values['institution_code']) }}">
                        </div>
                        <div class="form-group">
                            <label for="institution_phone">الهاتف</label>
                            <input id="institution_phone" name="institution_phone" class="form-control" value="{{ old('institution_phone', $values['institution_phone']) }}">
                        </div>
                        <div class="form-group col-span-full">
                            <label for="institution_address">العنوان</label>
                            <input id="institution_address" name="institution_address" class="form-control" value="{{ old('institution_address', $values['institution_address']) }}">
                        </div>
                        <div class="form-group col-span-full">
                            <label for="institution_email">البريد الإلكتروني</label>
                            <input id="institution_email" type="email" name="institution_email" class="form-control" value="{{ old('institution_email', $values['institution_email']) }}">
                        </div>
                        <div class="form-group col-span-full">
                            <label for="logo">شعار المؤسسة</label>
                            @if(!empty($values['institution_logo']))
                                <div class="mb-1"><img src="{{ route('settings.logo') }}" alt="الشعار" style="max-height:70px;"></div>
                                <label class="form-check mb-1">
                                    <input type="checkbox" name="remove_logo" value="1">
                                    <span>حذف الشعار الحالي</span>
                                </label>
                            @endif
                            <input id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="form-control">
                            <span class="form-hint">صيغة png أو jpg أو webp، بحجم أقصى 4 ميغابايت.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="card mb-2">
                    <div class="card-header"><h3><x-icon name="credit-card" /> إعدادات الأسعار</h3></div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="currency">العملة</label>
                                <input id="currency" name="currency" class="form-control" value="{{ old('currency', $values['currency']) }}">
                            </div>
                            <div class="form-group">
                                <label for="meal_price">سعر الوجبة (للمتربص) <span class="text-danger">*</span></label>
                                <input id="meal_price" type="number" step="0.01" min="0" name="meal_price" class="form-control" value="{{ old('meal_price', $values['meal_price']) }}" required>
                            </div>
                            <div class="form-group">
                                <label for="employee_meal_price">سعر وجبة الموظف <span class="text-danger">*</span></label>
                                <input id="employee_meal_price" type="number" step="0.01" min="0" name="employee_meal_price" class="form-control" value="{{ old('employee_meal_price', $values['employee_meal_price']) }}" required>
                                <span class="form-hint">يُستخدم لحساب سدادات الموظفين.</span>
                            </div>
                            <div class="form-group">
                                <label for="days_per_month">عدد أيام الشهر <span class="text-danger">*</span></label>
                                <input id="days_per_month" type="number" min="1" max="31" name="days_per_month" class="form-control" value="{{ old('days_per_month', $values['days_per_month']) }}" required>
                                <span class="form-hint">يُستخدم لحساب سعر الشهر عند تركه فارغًا.</span>
                            </div>
                            <div class="form-group">
                                <label for="price_month">سعر الشهر (اختياري)</label>
                                <input id="price_month" type="number" step="0.01" min="0" name="price_month" class="form-control" value="{{ old('price_month', $values['price_month']) }}">
                                <span class="form-hint">إن تُرك فارغًا: سعر الوجبة × عدد أيام الشهر.</span>
                            </div>
                            <div class="form-group">
                                <label for="price_quarter">سعر الثلاثي (اختياري)</label>
                                <input id="price_quarter" type="number" step="0.01" min="0" name="price_quarter" class="form-control" value="{{ old('price_quarter', $values['price_quarter']) }}">
                                <span class="form-hint">إن تُرك فارغًا: سعر الشهر × 3.</span>
                            </div>
                            <div class="form-group">
                                <label for="employee_price_month">سعر شهر الموظف (اختياري)</label>
                                <input id="employee_price_month" type="number" step="0.01" min="0" name="employee_price_month" class="form-control" value="{{ old('employee_price_month', $values['employee_price_month']) }}">
                                <span class="form-hint">إن تُرك فارغًا: سعر وجبة الموظف × عدد أيام الشهر.</span>
                            </div>
                            <div class="form-group">
                                <label for="employee_price_quarter">سعر ثلاثي الموظف (اختياري)</label>
                                <input id="employee_price_quarter" type="number" step="0.01" min="0" name="employee_price_quarter" class="form-control" value="{{ old('employee_price_quarter', $values['employee_price_quarter']) }}">
                                <span class="form-hint">إن تُرك فارغًا: سعر شهر الموظف × 3.</span>
                            </div>
                            <div class="form-group">
                                <label for="expiry_alert_days">مدة التنبيه قبل انتهاء السداد (أيام) <span class="text-danger">*</span></label>
                                <input id="expiry_alert_days" type="number" min="1" max="90" name="expiry_alert_days" class="form-control" value="{{ old('expiry_alert_days', $values['expiry_alert_days']) }}" required>
                                <span class="form-hint">تُنبَّه الإدارة قبل انتهاء السداد بهذا العدد من الأيام.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3><x-icon name="print" /> الطباعة والنسخ الاحتياطي</h3></div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="print_margin_mm">هامش الطباعة (مم) <span class="text-danger">*</span></label>
                                <input id="print_margin_mm" type="number" min="0" max="40" name="print_margin_mm" class="form-control" value="{{ old('print_margin_mm', $values['print_margin_mm']) }}" required>
                            </div>
                            <div class="form-group">
                                <label for="print_cards_per_page">عدد البطاقات في الصفحة <span class="text-danger">*</span></label>
                                <input id="print_cards_per_page" type="number" min="1" max="20" name="print_cards_per_page" class="form-control" value="{{ old('print_cards_per_page', $values['print_cards_per_page']) }}" required>
                            </div>
                            <div class="form-group">
                                <label for="backup_retention">عدد النسخ الاحتياطية المحفوظة <span class="text-danger">*</span></label>
                                <input id="backup_retention" type="number" min="1" max="100" name="backup_retention" class="form-control" value="{{ old('backup_retention', $values['backup_retention']) }}" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $hasErrors = isset($errors) && $errors->any();
            $cVal = static function (string $key, string $default = '') use ($values, $hasErrors): string {
                return $hasErrors ? (string) old($key, '') : (string) ($values[$key] ?? $default);
            };
            $cChk = static function (string $key) use ($values, $hasErrors): string {
                $val = $hasErrors ? old($key) : ($values[$key] ?? null);

                return in_array((string) $val, ['1', 'on', 'true', 'yes'], true) ? 'checked' : '';
            };

            // تسميات حقول البيانات تختلف حسب فئة البطاقة.
            $rowLabels = [
                'trainee' => [
                    'card_show_reg_no' => 'رقم التسجيل',
                    'card_show_name' => 'الاسم واللقب',
                    'card_show_birth' => 'تاريخ الميلاد',
                    'card_show_specialty' => 'الاختصاص',
                    'card_show_period' => 'مدة التكوين',
                ],
                'employee' => [
                    'card_show_reg_no' => 'رقم الموظف',
                    'card_show_name' => 'الاسم واللقب',
                    'card_show_birth' => 'تاريخ الميلاد',
                    'card_show_specialty' => 'الرتبة',
                    'card_show_period' => 'تاريخ التوظيف',
                ],
            ];

            // بيانات تجريبية للمعاينة الحيّة.
            $previewCode = ['trainee' => '2396', 'employee' => 'EMP-014'];
            $previewRows = [
                'trainee' => [
                    ['setting' => 'card_show_reg_no', 'k' => 'رقم التسجيل', 'v' => '2396'],
                    ['setting' => 'card_show_name', 'k' => 'الاسم واللقب', 'v' => 'محمد بن أحمد'],
                    ['setting' => 'card_show_birth', 'k' => 'الميلاد', 'v' => '1999-05-14 — الجزائر'],
                    ['setting' => 'card_show_specialty', 'k' => 'الاختصاص', 'v' => 'إعلام آلي'],
                    ['setting' => 'card_show_period', 'k' => 'مدة التكوين', 'v' => '2025-09-01 ← 2026-06-30'],
                ],
                'employee' => [
                    ['setting' => 'employee_card_show_reg_no', 'k' => 'رقم الموظف', 'v' => 'EMP-014'],
                    ['setting' => 'employee_card_show_name', 'k' => 'الاسم واللقب', 'v' => 'سعاد مرابط'],
                    ['setting' => 'employee_card_show_birth', 'k' => 'الميلاد', 'v' => '1988-02-09 — وهران'],
                    ['setting' => 'employee_card_show_specialty', 'k' => 'الرتبة', 'v' => 'متصرف إداري'],
                    ['setting' => 'employee_card_show_period', 'k' => 'تاريخ التوظيف', 'v' => '2015-10-01'],
                ],
            ];
            $previewBackRows = [
                'trainee' => [
                    ['k' => 'الاسم واللقب', 'v' => 'محمد بن أحمد'],
                    ['k' => 'الاختصاص', 'v' => 'إعلام آلي'],
                    ['k' => 'رقم التعريف', 'v' => '2396'],
                ],
                'employee' => [
                    ['k' => 'الاسم واللقب', 'v' => 'سعاد مرابط'],
                    ['k' => 'الرتبة', 'v' => 'متصرف إداري'],
                    ['k' => 'رقم التعريف', 'v' => 'EMP-014'],
                ],
            ];
        @endphp

        <div class="card mt-2" id="cardDesign" data-fonts='@json(\App\Services\SettingsService::CARD_FONTS)'>
            <div class="card-header"><h3><x-icon name="credit-card" /> تصميم بطاقة الطابعة</h3></div>
            <div class="card-body">
                <div class="card-type-switch" role="tablist">
                    @foreach($cardTypes as $typeKey => $type)
                        <button type="button" class="card-type-btn {{ $loop->first ? 'is-active' : '' }}" data-card-type-btn="{{ $typeKey }}">
                            <x-icon name="credit-card" size="15" /> {{ $type['label'] }}
                        </button>
                    @endforeach
                </div>

                @foreach($cardTypes as $typeKey => $type)
                    @php
                        $p = $type['prefix'];
                        $id = $typeKey.'_';
                    @endphp
                    <div class="card-type-panel {{ $loop->first ? '' : 'is-hidden' }}" data-card-type-panel="{{ $typeKey }}">
                        @include('partials.card-style', ['scope' => '.card-preview-'.$typeKey, 'prefix' => $p, 'emitFonts' => true])

                        <div class="card-design-grid">
                            {{-- المعاينة الحيّة --}}
                            <div class="card-preview-pane card-preview-{{ $typeKey }}">
                                <div class="card-preview-stage">
                                    <span class="stage-label">الوجه الأمامي</span>
                                    @include('partials.card-front', [
                                        'prefix' => $p,
                                        'code' => $previewCode[$typeKey],
                                        'photoUrl' => null,
                                        'logoPlaceholder' => true,
                                        'rows' => $previewRows[$typeKey],
                                    ])
                                    <span class="stage-label">الوجه الخلفي</span>
                                    @include('partials.card-back', [
                                        'prefix' => $p,
                                        'code' => $previewCode[$typeKey],
                                        'rows' => $previewBackRows[$typeKey],
                                    ])
                                </div>
                                <span class="form-hint">تُحدَّث المعاينة فوريًا عند تغيير أي خيار. لكل فئة تصميمها المستقل.</span>
                            </div>

                            {{-- أدوات التصميم --}}
                            <div>
                                {{-- التسمية والترويسة --}}
                                <div class="design-block">
                                    <div class="design-sub">التسمية والترويسة</div>
                                    <div class="form-grid">
                                        <div class="form-group col-span-full">
                                            <label for="{{ $id }}card_label">تسمية البطاقة</label>
                                            <input id="{{ $id }}card_label" name="{{ $p }}card_label" class="form-control"
                                                   value="{{ $cVal($p.'card_label') }}"
                                                   data-text-target='[data-card-part="label"]'>
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_label_font">خط التسمية</label>
                                            <select id="{{ $id }}card_label_font" name="{{ $p }}card_label_font" class="form-control" data-font-var="--card-label-font">
                                                @foreach($cardFonts as $fontKey => $font)
                                                    <option value="{{ $fontKey }}" @selected($cVal($p.'card_label_font', 'reem') === $fontKey)>{{ $font['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_label_color">لون التسمية</label>
                                            <input id="{{ $id }}card_label_color" type="color" name="{{ $p }}card_label_color" class="form-control"
                                                   value="{{ $cVal($p.'card_label_color', '#0c5f75') }}" data-cvar="--card-label-color">
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_label_size">حجم تسمية البطاقة (pt)</label>
                                            <input id="{{ $id }}card_label_size" type="number" step="0.1" min="1" max="200" name="{{ $p }}card_label_size" class="form-control"
                                                   value="{{ $cVal($p.'card_label_size', '7.6') }}" data-cvar="--card-label-size" data-unit="pt">
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_official_size">حجم النص الرسمي (pt)</label>
                                            <input id="{{ $id }}card_official_size" type="number" step="0.1" min="1" max="200" name="{{ $p }}card_official_size" class="form-control"
                                                   value="{{ $cVal($p.'card_official_size', '6.4') }}" data-cvar="--card-official-size" data-unit="pt">
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_inst_size">حجم اسم المؤسسة (pt)</label>
                                            <input id="{{ $id }}card_inst_size" type="number" step="0.1" min="1" max="200" name="{{ $p }}card_inst_size" class="form-control"
                                                   value="{{ $cVal($p.'card_inst_size', '7.6') }}" data-cvar="--card-inst-size" data-unit="pt">
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_logo_size">حجم الشعار (مم)</label>
                                            <input id="{{ $id }}card_logo_size" type="number" step="0.5" min="0" max="200" name="{{ $p }}card_logo_size" class="form-control"
                                                   value="{{ $cVal($p.'card_logo_size', '9') }}" data-cvar="--card-logo-size" data-unit="mm">
                                        </div>
                                    </div>
                                    <div class="checks mt-1">
                                        <label class="form-check">
                                            <input type="checkbox" name="{{ $p }}card_label_show" value="1" {{ $cChk($p.'card_label_show') }}
                                                   data-toggle-target='[data-card-part="label"]'>
                                            <span>إظهار تسمية البطاقة</span>
                                        </label>
                                        <label class="form-check">
                                            <input type="checkbox" name="{{ $p }}card_logo_show" value="1" {{ $cChk($p.'card_logo_show') }}
                                                   data-toggle-target='[data-card-part="logo"]'>
                                            <span>إظهار الشعار</span>
                                        </label>
                                    </div>
                                </div>

                                {{-- الخط والحقول --}}
                                <div class="design-block">
                                    <div class="design-sub">الخط والحقول</div>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="{{ $id }}card_font_family">خط البطاقة العام</label>
                                            <select id="{{ $id }}card_font_family" name="{{ $p }}card_font_family" class="form-control" data-font-var="--card-font">
                                                @foreach($cardFonts as $fontKey => $font)
                                                    <option value="{{ $fontKey }}" @selected($cVal($p.'card_font_family', 'cairo') === $fontKey)>{{ $font['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_font_size">الحجم الأساسي (pt)</label>
                                            <input id="{{ $id }}card_font_size" type="number" step="0.1" min="1" max="200" name="{{ $p }}card_font_size" class="form-control"
                                                   value="{{ $cVal($p.'card_font_size', '8.2') }}" data-cvar="--card-font-size" data-unit="pt">
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_fields_size">حجم حقول البيانات (pt)</label>
                                            <input id="{{ $id }}card_fields_size" type="number" step="0.1" min="1" max="200" name="{{ $p }}card_fields_size" class="form-control"
                                                   value="{{ $cVal($p.'card_fields_size', '7.4') }}" data-cvar="--card-fields-size" data-unit="pt">
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_field_label_w">عرض عمود العنوان (مم)</label>
                                            <input id="{{ $id }}card_field_label_w" type="number" step="0.5" min="0" max="200" name="{{ $p }}card_field_label_w" class="form-control"
                                                   value="{{ $cVal($p.'card_field_label_w', '15') }}" data-cvar="--card-k-w" data-unit="mm">
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_photo_w">عرض الصورة (مم)</label>
                                            <input id="{{ $id }}card_photo_w" type="number" step="0.5" min="0" max="200" name="{{ $p }}card_photo_w" class="form-control"
                                                   value="{{ $cVal($p.'card_photo_w', '17') }}" data-cvar="--card-photo-w" data-unit="mm">
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_photo_h">ارتفاع الصورة (مم)</label>
                                            <input id="{{ $id }}card_photo_h" type="number" step="0.5" min="0" max="200" name="{{ $p }}card_photo_h" class="form-control"
                                                   value="{{ $cVal($p.'card_photo_h', '21') }}" data-cvar="--card-photo-h" data-unit="mm">
                                        </div>
                                    </div>
                                    <div class="checks mt-1">
                                        <label class="form-check">
                                            <input type="checkbox" name="{{ $p }}card_photo_show" value="1" {{ $cChk($p.'card_photo_show') }}
                                                   data-toggle-target='[data-card-part="photo"]'>
                                            <span>إظهار الصورة</span>
                                        </label>
                                        @foreach(['card_show_reg_no', 'card_show_name', 'card_show_birth', 'card_show_specialty', 'card_show_period'] as $rowKey)
                                            <label class="form-check">
                                                <input type="checkbox" name="{{ $p }}{{ $rowKey }}" value="1" {{ $cChk($p.$rowKey) }}
                                                       data-toggle-target='[data-card-row="{{ $p }}{{ $rowKey }}"]'>
                                                <span>{{ $rowLabels[$typeKey][$rowKey] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- الباركود ورمز QR والتذييل --}}
                                <div class="design-block">
                                    <div class="design-sub">الباركود ورمز QR والتذييل</div>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="{{ $id }}card_barcode_h">ارتفاع الباركود (مم)</label>
                                            <input id="{{ $id }}card_barcode_h" type="number" step="0.5" min="0" max="200" name="{{ $p }}card_barcode_h" class="form-control"
                                                   value="{{ $cVal($p.'card_barcode_h', '7') }}" data-cvar="--card-barcode-h" data-unit="mm">
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_qr_size">حجم رمز QR (مم)</label>
                                            <input id="{{ $id }}card_qr_size" type="number" step="0.5" min="0" max="200" name="{{ $p }}card_qr_size" class="form-control"
                                                   value="{{ $cVal($p.'card_qr_size', '22') }}" data-cvar="--card-qr-size" data-unit="mm">
                                        </div>
                                        <div class="form-group col-span-full">
                                            <label for="{{ $id }}card_foot_label">تسمية التذييل</label>
                                            <input id="{{ $id }}card_foot_label" name="{{ $p }}card_foot_label" class="form-control"
                                                   value="{{ $cVal($p.'card_foot_label') }}"
                                                   data-text-target='[data-card-part="foot_label"]'>
                                        </div>
                                    </div>
                                    <div class="checks mt-1">
                                        <label class="form-check">
                                            <input type="checkbox" name="{{ $p }}card_barcode_show" value="1" {{ $cChk($p.'card_barcode_show') }}
                                                   data-toggle-target='[data-card-part="barcode"]'>
                                            <span>إظهار الباركود</span>
                                        </label>
                                        <label class="form-check">
                                            <input type="checkbox" name="{{ $p }}card_qr_show" value="1" {{ $cChk($p.'card_qr_show') }}
                                                   data-toggle-target='[data-card-part="qr"]'>
                                            <span>إظهار رمز QR</span>
                                        </label>
                                        <label class="form-check">
                                            <input type="checkbox" name="{{ $p }}card_foot_show" value="1" {{ $cChk($p.'card_foot_show') }}
                                                   data-toggle-target='[data-card-part="foot"]'>
                                            <span>إظهار التذييل</span>
                                        </label>
                                    </div>
                                </div>

                                {{-- الإطار والألوان --}}
                                <div class="design-block">
                                    <div class="design-sub">الإطار والألوان والأبعاد</div>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="{{ $id }}card_w">عرض البطاقة (مم)</label>
                                            <input id="{{ $id }}card_w" type="number" step="1" min="20" max="200" name="{{ $p }}card_w" class="form-control"
                                                   value="{{ $cVal($p.'card_w', '96') }}" data-cvar="--card-w" data-unit="mm">
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_h">ارتفاع البطاقة (مم)</label>
                                            <input id="{{ $id }}card_h" type="number" step="1" min="20" max="200" name="{{ $p }}card_h" class="form-control"
                                                   value="{{ $cVal($p.'card_h', '54') }}" data-cvar="--card-h" data-unit="mm">
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_radius">استدارة الحواف (مم)</label>
                                            <input id="{{ $id }}card_radius" type="number" step="0.5" min="0" max="200" name="{{ $p }}card_radius" class="form-control"
                                                   value="{{ $cVal($p.'card_radius', '3') }}" data-cvar="--card-radius" data-unit="mm">
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_border_w">سماكة الإطار (مم)</label>
                                            <input id="{{ $id }}card_border_w" type="number" step="0.1" min="0" max="200" name="{{ $p }}card_border_w" class="form-control"
                                                   value="{{ $cVal($p.'card_border_w', '0.3') }}" data-cvar="--card-border-w" data-unit="mm">
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_border_color">لون الإطار</label>
                                            <input id="{{ $id }}card_border_color" type="color" name="{{ $p }}card_border_color" class="form-control"
                                                   value="{{ $cVal($p.'card_border_color', '#94a3b8') }}" data-cvar="--card-border-color">
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_primary">اللون الأساسي (خطوط/فواصل)</label>
                                            <input id="{{ $id }}card_primary" type="color" name="{{ $p }}card_primary" class="form-control"
                                                   value="{{ $cVal($p.'card_primary', '#0e7490') }}" data-cvar="--card-primary">
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ $id }}card_header_bg">خلفية الترويسة</label>
                                            <input id="{{ $id }}card_header_bg" type="color" name="{{ $p }}card_header_bg" class="form-control"
                                                   value="{{ $cVal($p.'card_header_bg', '#f8fafc') }}" data-cvar="--card-header-bg">
                                        </div>
                                    </div>
                                </div>

                                {{-- الوجه الخلفي --}}
                                <div class="design-block">
                                    <div class="design-sub">الوجه الخلفي</div>
                                    <div class="form-grid">
                                        <div class="form-group col-span-full">
                                            <label for="{{ $id }}card_back_note">ملاحظة الوجه الخلفي</label>
                                            <input id="{{ $id }}card_back_note" name="{{ $p }}card_back_note" class="form-control"
                                                   value="{{ $cVal($p.'card_back_note') }}"
                                                   data-text-target='[data-card-part="back_note"]'>
                                        </div>
                                    </div>
                                    <div class="checks mt-1">
                                        <label class="form-check">
                                            <input type="checkbox" name="{{ $p }}card_back_info_show" value="1" {{ $cChk($p.'card_back_info_show') }}
                                                   data-toggle-target='[data-card-part="back_info"]'>
                                            <span>إظهار معلومات الوجه الخلفي</span>
                                        </label>
                                        <label class="form-check">
                                            <input type="checkbox" name="{{ $p }}card_back_note_show" value="1" {{ $cChk($p.'card_back_note_show') }}
                                                   data-toggle-target='[data-card-part="back_note"]'>
                                            <span>إظهار الملاحظة</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-2">
            <button type="submit" class="btn btn-primary"><x-icon name="save" /> حفظ الإعدادات</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            var root = document.getElementById('cardDesign');
            if (!root) { return; }

            var fonts = {};
            try { fonts = JSON.parse(root.getAttribute('data-fonts') || '{}'); } catch (e) { fonts = {}; }

            function paint(panel) {
                var preview = panel.querySelector('.card-preview-pane');
                if (!preview) { return; }

                panel.querySelectorAll('[data-cvar]').forEach(function (el) {
                    var unit = el.getAttribute('data-unit') || '';
                    preview.style.setProperty(el.getAttribute('data-cvar'), String(el.value).trim() + unit);
                });
                panel.querySelectorAll('select[data-font-var]').forEach(function (el) {
                    var font = fonts[el.value];
                    if (font && font.stack) { preview.style.setProperty(el.getAttribute('data-font-var'), font.stack); }
                });
                panel.querySelectorAll('input[type=checkbox][data-toggle-target]').forEach(function (el) {
                    preview.querySelectorAll(el.getAttribute('data-toggle-target')).forEach(function (target) {
                        target.classList.toggle('is-off', !el.checked);
                    });
                });
                panel.querySelectorAll('[data-text-target]').forEach(function (el) {
                    preview.querySelectorAll(el.getAttribute('data-text-target')).forEach(function (target) {
                        target.textContent = el.value;
                    });
                });
            }

            var panels = root.querySelectorAll('[data-card-type-panel]');

            panels.forEach(function (panel) {
                panel.querySelectorAll('input, select').forEach(function (el) {
                    el.addEventListener('input', function () { paint(panel); });
                    el.addEventListener('change', function () { paint(panel); });
                });
                paint(panel);
            });

            root.querySelectorAll('[data-card-type-btn]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var type = btn.getAttribute('data-card-type-btn');

                    root.querySelectorAll('[data-card-type-btn]').forEach(function (b) {
                        b.classList.toggle('is-active', b === btn);
                    });
                    panels.forEach(function (panel) {
                        panel.classList.toggle('is-hidden', panel.getAttribute('data-card-type-panel') !== type);
                    });
                });
            });
        })();
    </script>
@endpush
