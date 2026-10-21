@if($code === '')
    <div class="alert alert-info">
        <x-icon name="scan" />
        <div>امسح رمز QR أو الباركود، أو أدخل رقم التسجيل / الرقم الإلكتروني / رقم الموظف يدويًا.</div>
    </div>
@elseif(! $person)
    <div class="serve-result fail">لا يوجد سجل مطابق للرمز: {{ $code }}</div>
@else
    <div class="person-card mb-2">
        @if($person['photo'])
            <img class="photo" src="{{ $person['photo'] }}" alt="">
        @else
            <div class="photo"></div>
        @endif

        <div style="flex:1;">
            <div class="p-name">{{ $person['name'] }}</div>
            <div class="subtle">{{ $person['subtitle'] }}</div>
            <div class="check-row">
                <span class="badge badge-primary">{{ $person['identifier'] }}</span>
                <span class="badge badge-info">{{ $person['type'] === 'employee' ? 'موظف' : 'متربص' }}</span>
                @if($person['type'] === 'trainee')
                    @if($person['has_payment'])
                        <span class="badge badge-success">سداد ساري</span>
                    @else
                        <span class="badge badge-danger">لا يوجد سداد ساري</span>
                    @endif
                @endif
            </div>
        </div>
    </div>

    @if($person['allowed'])
        <div class="serve-result ok mb-2">مؤهل لتسليم الوجبة ✅</div>
        <form method="POST" action="{{ $person['serve_url'] }}">
            @csrf
            <button class="btn btn-success btn-lg btn-block"><x-icon name="check" /> تسجيل وتسليم الوجبة</button>
        </form>
    @else
        <div class="serve-result fail mb-2">{{ $person['reason'] ?: 'غير مؤهل لتسليم الوجبة' }}</div>

        @if($person['type'] === 'trainee' && ! $person['has_meal_today'] && $person['pay_url'])
            <form method="POST" action="{{ $person['pay_url'] }}" data-confirm="تسجيل سداد وجبة اليوم لهذا المتربص؟">
                @csrf
                <button class="btn btn-primary btn-lg btn-block"><x-icon name="credit-card" /> تسجيل سداد وجبة اليوم</button>
            </form>
            <p class="form-hint mt-1">قد يبقى السداد معلقًا حتى يعتمده المدير أو المراقب حسب صلاحيتك.</p>
        @endif
    @endif

    <p class="mt-2"><a href="{{ $person['profile_url'] }}" class="subtle">عرض الملف الكامل</a></p>
@endif
