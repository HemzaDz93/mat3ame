@php
    /**
     * الوجه الأمامي لبطاقة التعريف.
     * $code  : رمز البطاقة (باركود + التذييل)
     * $rows  : صفوف الحقول [['setting' => '', 'k' => '', 'v' => ''], ...]
     * $photoUrl : رابط صورة المتربص/الموظف أو null
     * $logoPlaceholder : إظهار مستطيل بديل للشعار عند غيابه (للمعاينة)
     * $prefix : بادئة مفاتيح إعدادات الفئة ('' للمتربصين، 'employee_' للموظفين)
     */
    $rows = $rows ?? [];
    $photoUrl = $photoUrl ?? null;
    $logoPlaceholder = $logoPlaceholder ?? false;
    $prefix = $prefix ?? '';
@endphp

<div class="id-card">
    <div class="card-top">
        <div class="official">
            <div class="rep">{{ setting('country', 'الجمهورية الجزائرية الديمقراطية الشعبية') }}</div>
            <div>{{ setting('ministry', 'وزارة التكوين والتعليم المهنيين') }}</div>
            @if(setting('directorate'))
                <div>{{ setting('directorate') }}</div>
            @endif
            <div class="inst">{{ setting('institution_name', 'منصة الزاوية') }}</div>
            <div class="card-kind {{ setting_on($prefix.'card_label_show') ? '' : 'is-off' }}" data-card-part="label">{{ setting($prefix.'card_label', 'بطاقة المطعم') }}</div>
        </div>
        @if(setting_on($prefix.'card_logo_show'))
            @if(setting('institution_logo'))
                <img class="logo" src="{{ route('settings.logo') }}" alt="الشعار" data-card-part="logo">
            @elseif($logoPlaceholder)
                <div class="logo logo-placeholder" data-card-part="logo">شعار</div>
            @endif
        @endif
    </div>

    <div class="card-body">
        <div class="fields">
            @foreach($rows as $row)
                <div class="row {{ setting_on($row['setting']) ? '' : 'is-off' }}" data-card-row="{{ $row['setting'] }}">
                    <span class="k">{{ $row['k'] }}</span>
                    <span class="v">{{ $row['v'] }}</span>
                </div>
            @endforeach
        </div>
        <div class="photo-slot {{ setting_on($prefix.'card_photo_show') ? '' : 'is-off' }}" data-card-part="photo">
            @if($photoUrl)
                <img class="photo" src="{{ $photoUrl }}" alt="">
            @else
                <div class="photo"></div>
            @endif
        </div>
    </div>

    <div class="barcode-wrap {{ setting_on($prefix.'card_barcode_show') ? '' : 'is-off' }}" data-card-part="barcode">
        <img src="{{ route('barcode.code128', $code) }}" alt="باركود">
        <div class="code">{{ $code }}</div>
    </div>
    <div class="card-foot {{ setting_on($prefix.'card_foot_show') ? '' : 'is-off' }}" data-card-part="foot"><span data-card-part="foot_label">{{ setting($prefix.'card_foot_label', 'رقم التعريف') }}</span>: {{ $code }}</div>
</div>
