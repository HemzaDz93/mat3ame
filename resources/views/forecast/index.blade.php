@extends('layouts.app')

@section('title', 'إعلان الوجبات')
@section('page_title', 'إعلان عدد وجبات المتربصين')

@section('content')
    <div class="stat-grid">
        <div class="stat-card is-success">
            <div class="stat-icon"><x-icon name="utensils" /></div>
            <div>
                <div class="stat-value">{{ number_format($todayForecast?->expected_count ?? 0) }}</div>
                <div class="stat-label">العدد المتوقّع اليوم</div>
            </div>
        </div>
        <div class="stat-card is-info">
            <div class="stat-icon"><x-icon name="calendar" /></div>
            <div>
                <div class="stat-value">{{ number_format($tomorrowForecast?->expected_count ?? 0) }}</div>
                <div class="stat-label">العدد المتوقّع غدًا</div>
            </div>
        </div>
    </div>

    <div class="card mb-2">
        <div class="card-header"><h3>تسجيل العدد المتوقّع</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('forecast.store') }}" class="form-grid">
                @csrf
                <div class="form-group">
                    <label for="day">التاريخ</label>
                    <input id="day" type="date" name="day" class="form-control @error('day') is-invalid @enderror"
                           value="{{ old('day', now()->toDateString()) }}" required>
                    @error('day')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="expected_count">العدد المتوقّع لوجبات المتربصين</label>
                    <input id="expected_count" type="number" min="0" name="expected_count"
                           class="form-control @error('expected_count') is-invalid @enderror"
                           value="{{ old('expected_count') }}" required>
                    @error('expected_count')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="note">ملاحظة (اختياري)</label>
                    <input id="note" name="note" class="form-control"
                           value="{{ old('note') }}" placeholder="مثال: رحلة ميدانية">
                    @error('note')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <button class="btn btn-primary"><x-icon name="save" /> حفظ العدد المتوقّع</button>
                </div>
            </form>
            <div class="form-hint mt-1">يُحدَّث السجل تلقائيًا إذا كان التاريخ مسجّلًا مسبقًا، ويراه مسيّر المطعم في منصة المطعم وتقرير المطعم.</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>الإعلانات المسجّلة</h3></div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>العدد المتوقّع</th>
                        <th>الوجبات المنفّذة</th>
                        <th>الفرق</th>
                        <th>ملاحظة</th>
                        <th>أُعلن بواسطة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forecasts as $forecast)
                        @php
                            $key = $forecast->day->toDateString();
                            $done = (int) ($served[$key] ?? 0);
                            $diff = $done - (int) $forecast->expected_count;
                        @endphp
                        <tr>
                            <td class="cell-nowrap">{{ $key }}</td>
                            <td class="cell-nowrap">{{ number_format($forecast->expected_count) }}</td>
                            <td class="cell-nowrap">{{ number_format($done) }}</td>
                            <td class="cell-nowrap">
                                <span class="badge badge-{{ $diff === 0 ? 'success' : ($diff > 0 ? 'warning' : 'danger') }}">
                                    {{ $diff > 0 ? '+' : '' }}{{ number_format($diff) }}
                                </span>
                            </td>
                            <td>{{ $forecast->note ?: '—' }}</td>
                            <td class="cell-muted">{{ $forecast->updater?->name ?: '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('forecast.destroy', $forecast) }}"
                                      data-confirm="حذف توقّع يوم {{ $key }}؟">
                                    @csrf
                                    <button class="btn btn-danger btn-sm"><x-icon name="trash" /> حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty-state">لا توجد إعلانات مسجّلة بعد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $forecasts->links('partials.pagination') }}
    </div>
@endsection
