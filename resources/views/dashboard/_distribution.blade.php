@php
    $max = max(1, (int) ($rows->max('total') ?? 1));
@endphp
<div class="card">
    <div class="card-header"><h3>{{ $title }}</h3></div>
    <div class="card-body">
        @forelse($rows as $row)
            <div class="mb-1">
                <div class="d-flex justify-between">
                    <span>{{ $row->{$key} ?: '—' }}</span>
                    <strong>{{ number_format($row->total) }}</strong>
                </div>
                <div style="height:8px;background:#eef2f6;border-radius:6px;overflow:hidden;">
                    <div style="height:100%;width:{{ round($row->total / $max * 100) }}%;background:var(--c-accent);"></div>
                </div>
            </div>
        @empty
            <div class="subtle">لا توجد بيانات.</div>
        @endforelse
    </div>
</div>
