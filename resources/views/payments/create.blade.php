@extends('layouts.app')

@section('title', 'سداد جديد')
@section('page_title', 'تسجيل سداد')

@section('content')
    <div class="page-head">
        <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm"><x-icon name="arrow-right" /> رجوع</a>
        <h2>تسجيل سداد جديد</h2>
    </div>

    <div class="card mb-2" style="max-width:860px;">
        <div class="card-header"><h3>نوع السداد</h3></div>
        <div class="card-body">
            <div class="d-flex gap-1">
                <a href="{{ route('payments.create', ['type' => 'trainee']) }}"
                   class="btn {{ $type === 'trainee' ? 'btn-primary' : 'btn-secondary' }}">
                    <x-icon name="users" /> متربص
                </a>
                <a href="{{ route('payments.create', ['type' => 'employee']) }}"
                   class="btn {{ $type === 'employee' ? 'btn-primary' : 'btn-secondary' }}">
                    <x-icon name="user" /> موظف
                </a>
            </div>
        </div>
    </div>

    @if($type === 'employee')
        {{-- ------------------------- سداد موظف ------------------------- --}}
        @if(! $employee)
            <div class="card mb-2" style="max-width:860px;">
                <div class="card-header"><h3>اختيار الموظف</h3></div>
                <div class="card-body">
                    <form method="GET" class="filter-bar">
                        <input type="hidden" name="type" value="employee">
                        <div class="form-group grow">
                            <label>بحث</label>
                            <input name="q" value="{{ request('q') }}" class="form-control" placeholder="الاسم، اللقب، رقم الموظف" autofocus>
                        </div>
                        <div class="form-group"><button class="btn btn-secondary"><x-icon name="search" /> بحث</button></div>
                    </form>

                    <div class="table-wrap mt-2">
                        <table class="table">
                            <thead><tr><th>الاسم واللقب</th><th>رقم الموظف</th><th>الرتبة</th><th></th></tr></thead>
                            <tbody>
                                @forelse($results as $result)
                                    <tr>
                                        <td>{{ $result->full_name }}</td>
                                        <td class="cell-nowrap">{{ $result->emp_no ?: '—' }}</td>
                                        <td>{{ $result->rank ?: '—' }}</td>
                                        <td><a href="{{ route('payments.create', ['type' => 'employee', 'employee_id' => $result->id]) }}" class="btn btn-primary btn-sm">اختيار</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="empty-state">لا توجد نتائج.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($results->count() >= 30)
                        <div class="subtle mt-2">يُعرض أول 30 نتيجة، استخدم البحث لتضييق النتائج.</div>
                    @endif
                </div>
            </div>
        @else
            <div class="card" style="max-width:760px;">
                <div class="card-header"><h3>سداد للموظف: {{ $employee->full_name }}</h3></div>
                <div class="card-body">
                    <ul class="info-list mb-2">
                        <li><span class="k">رقم الموظف</span><span class="v">{{ $employee->emp_no ?: '—' }}</span></li>
                        <li><span class="k">الرتبة</span><span class="v">{{ $employee->rank ?: '—' }}</span></li>
                    </ul>

                    @php $direct = auth()->user()->canDecidePayments(); @endphp

                    <form method="POST" action="{{ $direct ? route('payments.employees.store-direct') : route('payments.employees.store') }}">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="plan">نوع السداد</label>
                                <select id="plan" name="plan" class="form-control" required>
                                    @foreach($plans as $value => $label)
                                        <option value="{{ $value }}" data-price="{{ $prices[$value] ?? 0 }}" @selected(old('plan') === $value)>
                                            {{ $label }} — {{ money($prices[$value] ?? 0) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="start_date">تاريخ البداية</label>
                                <input id="start_date" type="date" name="start_date" class="form-control" value="{{ old('start_date', today_date()) }}" required>
                            </div>
                            <div class="form-group col-span-full">
                                <label for="note">ملاحظة</label>
                                <textarea id="note" name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
                            </div>
                        </div>

                        @if(! $direct)
                            <div class="alert alert-info">
                                <x-icon name="info" />
                                <div>سيُسجَّل السداد بحالة «معلق» بانتظار اعتماد المدير أو المراقب.</div>
                            </div>
                        @else
                            <div class="alert alert-success">
                                <x-icon name="check-circle" />
                                <div>سيُسجَّل السداد معتمدًا مباشرة بصلاحيتك الحالية.</div>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary"><x-icon name="save" /> تسجيل السداد</button>
                    </form>
                </div>
            </div>
        @endif
    @else
        {{-- ------------------------- سداد متربص ------------------------- --}}
        @if(! $trainee)
            <div class="card mb-2" style="max-width:860px;">
                <div class="card-header"><h3>اختيار المتربص</h3></div>
                <div class="card-body">
                    <form method="GET" class="filter-bar">
                        <input type="hidden" name="type" value="trainee">
                        <div class="form-group">
                            <label>الاختصاص</label>
                            <select name="specialty" class="form-control" onchange="this.form.submit()">
                                <option value="">كل الاختصاصات</option>
                                @foreach($specialties as $specialty)
                                    <option value="{{ $specialty }}" @selected(request('specialty') === $specialty)>{{ $specialty }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group grow">
                            <label>بحث</label>
                            <input name="q" value="{{ request('q') }}" class="form-control" placeholder="الاسم، اللقب، رقم التسجيل، الرقم الإلكتروني، NIN">
                        </div>
                        <div class="form-group"><button class="btn btn-secondary"><x-icon name="search" /> بحث</button></div>
                    </form>

                    <div class="table-wrap mt-2">
                        <table class="table">
                            <thead><tr><th>الاسم واللقب</th><th>رقم التسجيل</th><th>الاختصاص</th><th></th></tr></thead>
                            <tbody>
                                @forelse($results as $result)
                                    <tr>
                                        <td>{{ $result->full_name }}</td>
                                        <td class="cell-nowrap">{{ $result->reg_no ?: ($result->electronic_reg_no ?: '—') }}</td>
                                        <td>{{ $result->specialty ?: '—' }}</td>
                                        <td><a href="{{ route('payments.create', ['type' => 'trainee', 'trainee_id' => $result->id]) }}" class="btn btn-primary btn-sm">اختيار</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="empty-state">لا توجد نتائج.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($results->count() >= 30)
                        <div class="subtle mt-2">يُعرض أول 30 نتيجة، استخدم البحث أو الاختصاص لتضييق النتائج.</div>
                    @endif
                </div>
            </div>
        @else
            <div class="card" style="max-width:760px;">
                <div class="card-header"><h3>سداد للمتربص: {{ $trainee->full_name }}</h3></div>
                <div class="card-body">
                    <ul class="info-list mb-2">
                        <li><span class="k">رقم التسجيل</span><span class="v">{{ $trainee->reg_no ?: '—' }}</span></li>
                        <li><span class="k">الرقم الإلكتروني</span><span class="v">{{ $trainee->electronic_reg_no ?: '—' }}</span></li>
                        <li><span class="k">الاختصاص</span><span class="v">{{ $trainee->specialty ?: '—' }}</span></li>
                    </ul>

                    @php $direct = auth()->user()->canDecidePayments(); @endphp

                    <form method="POST" action="{{ $direct ? route('payments.store-direct') : route('payments.store') }}">
                        @csrf
                        <input type="hidden" name="trainee_id" value="{{ $trainee->id }}">

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="plan">نوع السداد</label>
                                <select id="plan" name="plan" class="form-control" required>
                                    @foreach($plans as $value => $label)
                                        <option value="{{ $value }}" data-price="{{ $prices[$value] ?? 0 }}" @selected(old('plan') === $value)>
                                            {{ $label }} — {{ money($prices[$value] ?? 0) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="start_date">تاريخ البداية</label>
                                <input id="start_date" type="date" name="start_date" class="form-control" value="{{ old('start_date', today_date()) }}" required>
                            </div>
                            <div class="form-group col-span-full">
                                <label for="note">ملاحظة</label>
                                <textarea id="note" name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
                            </div>
                        </div>

                        @if(! $direct)
                            <div class="alert alert-info">
                                <x-icon name="info" />
                                <div>سيُسجَّل السداد بحالة «معلق» بانتظار اعتماد المدير أو المراقب.</div>
                            </div>
                        @else
                            <div class="alert alert-success">
                                <x-icon name="check-circle" />
                                <div>سيُسجَّل السداد معتمدًا مباشرة بصلاحيتك الحالية.</div>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary"><x-icon name="save" /> تسجيل السداد</button>
                    </form>
                </div>
            </div>
        @endif
    @endif
@endsection
