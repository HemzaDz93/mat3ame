@php $e = $employee; @endphp

<div class="card">
    <div class="card-body">
        <div class="form-grid">
            <div class="form-group">
                <label for="emp_no">رقم الموظف</label>
                <input id="emp_no" name="emp_no" class="form-control @error('emp_no') is-invalid @enderror" value="{{ old('emp_no', $e->emp_no) }}">
                <span class="form-hint">اتركه فارغًا لتوليد رقم تلقائي بصيغة EMP-XXXXXX.</span>
                @error('emp_no')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="last_name">اللقب <span class="text-danger">*</span></label>
                <input id="last_name" name="last_name" class="form-control" value="{{ old('last_name', $e->last_name) }}" required>
            </div>
            <div class="form-group">
                <label for="first_name">الاسم <span class="text-danger">*</span></label>
                <input id="first_name" name="first_name" class="form-control" value="{{ old('first_name', $e->first_name) }}" required>
            </div>
            <div class="form-group">
                <label for="birth_date">تاريخ الميلاد</label>
                <input id="birth_date" type="date" name="birth_date" class="form-control" value="{{ old('birth_date', optional($e->birth_date)->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label for="birth_place">مكان الميلاد</label>
                <input id="birth_place" name="birth_place" class="form-control" value="{{ old('birth_place', $e->birth_place) }}">
            </div>
            <div class="form-group">
                <label for="hire_date">تاريخ التوظيف</label>
                <input id="hire_date" type="date" name="hire_date" class="form-control" value="{{ old('hire_date', optional($e->hire_date)->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label for="rank">الرتبة</label>
                <input id="rank" name="rank" class="form-control" value="{{ old('rank', $e->rank) }}">
            </div>
            <div class="form-group">
                <label for="photo">الصورة</label>
                <input id="photo" type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="form-control">
            </div>
            <div class="form-group col-span-full">
                <label for="notes">ملاحظات</label>
                <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $e->notes) }}</textarea>
            </div>
        </div>
    </div>
</div>
