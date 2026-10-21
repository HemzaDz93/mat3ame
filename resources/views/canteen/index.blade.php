@extends('layouts.app')

@section('title', 'منصة المطعم')
@section('page_title', 'منصة المطعم')

@section('content')
    <div class="pos-shell">
        <div class="stat-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr));">
            <div class="stat-card is-success">
                <div class="stat-icon"><x-icon name="utensils" /></div>
                <div>
                    <div class="stat-value">{{ number_format($mealsToday) }}</div>
                    <div class="stat-label">وجبات المتربصين اليوم</div>
                </div>
            </div>
            <div class="stat-card is-info">
                <div class="stat-icon"><x-icon name="user" /></div>
                <div>
                    <div class="stat-value">{{ number_format($employeeMealsToday) }}</div>
                    <div class="stat-label">وجبات الموظفين اليوم</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><x-icon name="calendar" /></div>
                <div>
                    <div class="stat-value">{{ ar_date(today_date()) }}</div>
                    <div class="stat-label">تاريخ اليوم</div>
                </div>
            </div>
            <div class="stat-card is-warning">
                <div class="stat-icon"><x-icon name="clipboard" /></div>
                <div>
                    <div class="stat-value">{{ number_format($expectedToday) }}</div>
                    <div class="stat-label">الوجبات المتوقّعة اليوم</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><x-icon name="clipboard" /></div>
                <div>
                    <div class="stat-value">{{ number_format($expectedTomorrow) }}</div>
                    <div class="stat-label">الوجبات المتوقّعة غدًا</div>
                </div>
            </div>
        </div>

        <div class="card mb-2">
            <div class="card-body">
                <form method="GET" action="{{ route('canteen.index') }}" id="scan-form" class="scan-box">
                    <label class="form-hint">امسح الرمز أو أدخل الرقم يدويًا</label>
                    <input type="text" name="code" id="scan-input" class="form-control" autocomplete="off"
                           placeholder="QR / باركود / رقم التسجيل / رقم الموظف" value="{{ $code }}" autofocus>

                    <div class="btn-group-actions mt-2">
                        <button type="submit" class="btn btn-primary"><x-icon name="search" /> بحث</button>
                        <button type="button" class="btn btn-secondary" id="camera-btn"><x-icon name="scan" /> تشغيل الكاميرا</button>
                        <button type="button" class="btn btn-ghost" id="camera-stop" style="display:none;"><x-icon name="x" /> إيقاف الكاميرا</button>
                    </div>
                </form>

                <div id="camera-wrap" style="display:none; margin-top:12px;">
                    <video id="camera-video" style="width:100%; max-width:520px; border-radius:12px; background:#000;" playsinline muted></video>
                    <div class="form-hint" id="camera-status">جارٍ تشغيل الكاميرا...</div>
                </div>

                <div id="scan-message" class="form-hint mt-1"></div>
            </div>
        </div>

        <div class="card" id="result-card">
            <div class="card-body" id="result-body">
                @include('canteen._result', ['person' => $person, 'code' => $code])
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.CANTEEN = {
            findUrl: "{{ route('canteen.find') }}",
            csrf: "{{ csrf_token() }}",
        };
    </script>
    <script src="{{ asset('js/jsqr.js') }}"></script>
    <script src="{{ asset('js/scanner.js') }}"></script>
@endpush
