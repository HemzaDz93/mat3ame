@php
    /**
     * الوجه الخلفي لبطاقة التعريف.
     * $code : رمز البطاقة (رمز QR)
     * $rows : صفوف المعلومات [['k' => '', 'v' => ''], ...]
     * $prefix : بادئة مفاتيح إعدادات الفئة ('' للمتربصين، 'employee_' للموظفين)
     */
    $rows = $rows ?? [];
    $prefix = $prefix ?? '';
@endphp

<div class="id-card">
    <div class="card-back">
        <div class="qr-wrap {{ setting_on($prefix.'card_qr_show') ? '' : 'is-off' }}" data-card-part="qr">
            <img src="{{ route('barcode.qr', $code) }}" alt="رمز QR">
        </div>
        <div class="back-info {{ setting_on($prefix.'card_back_info_show') ? '' : 'is-off' }}" data-card-part="back_info">
            @foreach($rows as $row)
                <div class="row"><span class="k">{{ $row['k'] }}</span><br><b>{{ $row['v'] }}</b></div>
            @endforeach
        </div>
    </div>
    <div class="corner-note {{ setting_on($prefix.'card_back_note_show') ? '' : 'is-off' }}" data-card-part="back_note">{{ setting($prefix.'card_back_note', 'الوجه الخلفي') }}</div>
</div>
