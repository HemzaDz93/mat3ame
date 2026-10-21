@php $t = $trainee; @endphp

<div class="grid-2">
    <div class="card">
        <div class="card-header"><h3>الهوية</h3></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label for="reg_no">رقم التسجيل</label>
                    <input id="reg_no" name="reg_no" class="form-control" value="{{ old('reg_no', $t->reg_no) }}">
                </div>
                <div class="form-group">
                    <label for="electronic_reg_no">الرقم الإلكتروني</label>
                    <input id="electronic_reg_no" name="electronic_reg_no" class="form-control @error('electronic_reg_no') is-invalid @enderror" value="{{ old('electronic_reg_no', $t->electronic_reg_no) }}">
                    @error('electronic_reg_no')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="last_name">اللقب <span class="text-danger">*</span></label>
                    <input id="last_name" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $t->last_name) }}" required>
                    @error('last_name')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="first_name">الاسم <span class="text-danger">*</span></label>
                    <input id="first_name" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $t->first_name) }}" required>
                    @error('first_name')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="last_name_fr">اللقب (بالفرنسية)</label>
                    <input id="last_name_fr" name="last_name_fr" class="form-control" value="{{ old('last_name_fr', $t->last_name_fr) }}">
                </div>
                <div class="form-group">
                    <label for="first_name_fr">الاسم (بالفرنسية)</label>
                    <input id="first_name_fr" name="first_name_fr" class="form-control" value="{{ old('first_name_fr', $t->first_name_fr) }}">
                </div>
                <div class="form-group">
                    <label for="gender">الجنس</label>
                    <select id="gender" name="gender" class="form-control">
                        <option value="">—</option>
                        <option value="M" @selected(old('gender', $t->gender) === 'M')>ذكر</option>
                        <option value="F" @selected(old('gender', $t->gender) === 'F')>أنثى</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nin">رقم التعريف الوطني (NIN)</label>
                    <input id="nin" name="nin" class="form-control" value="{{ old('nin', $t->nin) }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>الميلاد والإقامة</h3></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label for="birth_date">تاريخ الميلاد</label>
                    <input id="birth_date" type="date" name="birth_date" class="form-control" value="{{ old('birth_date', optional($t->birth_date)->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label for="birth_place">مكان الميلاد</label>
                    <input id="birth_place" name="birth_place" class="form-control" value="{{ old('birth_place', $t->birth_place) }}">
                </div>
                <div class="form-group">
                    <label for="birth_wilaya">ولاية الميلاد</label>
                    <input id="birth_wilaya" name="birth_wilaya" class="form-control" value="{{ old('birth_wilaya', $t->birth_wilaya) }}">
                </div>
                <div class="form-group">
                    <label for="residence_wilaya">ولاية الإقامة</label>
                    <input id="residence_wilaya" name="residence_wilaya" class="form-control" value="{{ old('residence_wilaya', $t->residence_wilaya) }}">
                </div>
                <div class="form-group col-span-full">
                    <label for="address">العنوان</label>
                    <input id="address" name="address" class="form-control" value="{{ old('address', $t->address) }}">
                </div>
                <div class="form-group">
                    <label for="phone">الهاتف</label>
                    <input id="phone" name="phone" class="form-control" value="{{ old('phone', $t->phone) }}">
                </div>
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $t->email) }}">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid-2 mt-2">
    <div class="card">
        <div class="card-header"><h3>العائلة</h3></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label for="father_name">اسم الأب</label>
                    <input id="father_name" name="father_name" class="form-control" value="{{ old('father_name', $t->father_name) }}">
                </div>
                <div class="form-group">
                    <label for="mother_name">اسم الأم</label>
                    <input id="mother_name" name="mother_name" class="form-control" value="{{ old('mother_name', $t->mother_name) }}">
                </div>
                <div class="form-group">
                    <label for="mother_last_name">لقب الأم</label>
                    <input id="mother_last_name" name="mother_last_name" class="form-control" value="{{ old('mother_last_name', $t->mother_last_name) }}">
                </div>
                <div class="form-group">
                    <label for="special_needs">الاحتياجات الخاصة</label>
                    <input id="special_needs" name="special_needs" class="form-control" value="{{ old('special_needs', $t->special_needs) }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>التكوين</h3></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label for="specialty">الاختصاص</label>
                    <input id="specialty" name="specialty" class="form-control" value="{{ old('specialty', $t->specialty) }}">
                </div>
                <div class="form-group">
                    <label for="diploma">الشهادة</label>
                    <input id="diploma" name="diploma" class="form-control" value="{{ old('diploma', $t->diploma) }}">
                </div>
                <div class="form-group">
                    <label for="level">المستوى</label>
                    <input id="level" name="level" class="form-control" value="{{ old('level', $t->level) }}">
                </div>
                <div class="form-group">
                    <label for="training_mode">نمط التكوين</label>
                    <input id="training_mode" name="training_mode" class="form-control" value="{{ old('training_mode', $t->training_mode) }}">
                </div>
                <div class="form-group">
                    <label for="start_date">تاريخ بداية التكوين</label>
                    <input id="start_date" type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($t->start_date)->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label for="end_date">تاريخ نهاية التكوين</label>
                    <input id="end_date" type="date" name="end_date" class="form-control" value="{{ old('end_date', optional($t->end_date)->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label for="registered_at">تاريخ التسجيل</label>
                    <input id="registered_at" type="date" name="registered_at" class="form-control" value="{{ old('registered_at', optional($t->registered_at)->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label for="photo">الصورة الشخصية</label>
                    <input id="photo" type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="form-control">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-2">
    <div class="card-header"><h3>الحالة</h3></div>
    <div class="card-body">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-check">
                    <input type="hidden" name="file_accepted" value="0">
                    <input type="checkbox" name="file_accepted" value="1" @checked(old('file_accepted', $t->file_accepted))>
                    <span>الملف مقبول</span>
                </label>
            </div>
            <div class="form-group">
                <label class="form-check">
                    <input type="hidden" name="eligible" value="0">
                    <input type="checkbox" name="eligible" value="1" @checked(old('eligible', $t->eligible))>
                    <span>مؤهل</span>
                </label>
            </div>
            <div class="form-group">
                <label class="form-check">
                    <input type="hidden" name="deliberation_accepted" value="0">
                    <input type="checkbox" name="deliberation_accepted" value="1" @checked(old('deliberation_accepted', $t->deliberation_accepted))>
                    <span>مقبول في المداولة</span>
                </label>
            </div>
            <div class="form-group">
                <label class="form-check">
                    <input type="hidden" name="account_cancelled" value="0">
                    <input type="checkbox" name="account_cancelled" value="1" @checked(old('account_cancelled', $t->account_cancelled))>
                    <span>الحساب ملغى</span>
                </label>
            </div>
            <div class="form-group col-span-full">
                <label for="notes">ملاحظات</label>
                <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $t->notes) }}</textarea>
            </div>
        </div>
    </div>
</div>
