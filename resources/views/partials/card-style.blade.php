@php
    /**
     * يولّد متغيّرات تصميم بطاقة الطابعة انطلاقًا من إعدادات المنصة.
     *
     * $scope     : المحدّد الذي تُطبَّق عليه المتغيّرات (افتراضيًا :root).
     * $prefix    : بادئة مفاتيح الإعدادات الخاصة بالفئة ('' للمتربصين، 'employee_' للموظفين).
     * $emitFonts : تحميل خطوط جوجل المختارة (افتراضيًا نعم).
     */
    $scope = $scope ?? ':root';
    $prefix = $prefix ?? '';
    $emitFonts = $emitFonts ?? true;

    $fontKeys = array_keys(\App\Services\SettingsService::CARD_FONTS);

    $baseKey = (string) setting($prefix.'card_font_family', 'cairo');
    $baseKey = in_array($baseKey, $fontKeys, true) ? $baseKey : 'cairo';

    $labelKey = (string) setting($prefix.'card_label_font', 'reem');
    $labelKey = in_array($labelKey, $fontKeys, true) ? $labelKey : 'reem';

    // Cairo محمّل دائمًا في القالب، فلا نكرّره في رابط الخطوط.
    $families = array_values(array_diff(array_unique([
        \App\Services\SettingsService::CARD_FONTS[$baseKey]['google'],
        \App\Services\SettingsService::CARD_FONTS[$labelKey]['google'],
    ]), [\App\Services\SettingsService::CARD_FONTS['cairo']['google']]));

    $color = static function (string $key, string $default) use ($prefix): string {
        $value = (string) setting($prefix.$key, $default);

        return preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1 ? $value : $default;
    };

    $size = static fn (string $key, float $default): string => rtrim(rtrim(number_format((float) setting($prefix.$key, $default), 2, '.', ''), '0'), '.');
@endphp

@if($emitFonts && $families !== [])
    <link href="https://fonts.googleapis.com/css2?family={{ implode('&family=', $families) }}&display=swap" rel="stylesheet">
@endif

<style>
{{ $scope }} {
    --card-w: {{ $size('card_w', 96) }}mm;
    --card-h: {{ $size('card_h', 54) }}mm;
    --card-radius: {{ $size('card_radius', 3) }}mm;
    --card-border-w: {{ $size('card_border_w', 0.3) }}mm;
    --card-border-color: {{ $color('card_border_color', '#94a3b8') }};
    --card-primary: {{ $color('card_primary', '#0e7490') }};
    --card-header-bg: {{ $color('card_header_bg', '#f8fafc') }};
    --card-font: {!! \App\Services\SettingsService::CARD_FONTS[$baseKey]['stack'] !!};
    --card-font-size: {{ $size('card_font_size', 8.2) }}pt;
    --card-official-size: {{ $size('card_official_size', 6.4) }}pt;
    --card-inst-size: {{ $size('card_inst_size', 7.6) }}pt;
    --card-label-size: {{ $size('card_label_size', 7.6) }}pt;
    --card-label-font: {!! \App\Services\SettingsService::CARD_FONTS[$labelKey]['stack'] !!};
    --card-label-color: {{ $color('card_label_color', '#0c5f75') }};
    --card-logo-size: {{ $size('card_logo_size', 9) }}mm;
    --card-fields-size: {{ $size('card_fields_size', 7.4) }}pt;
    --card-k-w: {{ $size('card_field_label_w', 15) }}mm;
    --card-photo-w: {{ $size('card_photo_w', 17) }}mm;
    --card-photo-h: {{ $size('card_photo_h', 21) }}mm;
    --card-barcode-h: {{ $size('card_barcode_h', 7) }}mm;
    --card-qr-size: {{ $size('card_qr_size', 22) }}mm;
}
</style>
