<?php

namespace App\Http\Requests;

use App\Models\Trainee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TraineeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin', 'supervisor', 'staff') === true;
    }

    public function rules(): array
    {
        $trainee = $this->route('trainee');
        $id = $trainee instanceof Trainee ? $trainee->id : null;

        return [
            'reg_no' => [
                'nullable', 'string', 'max:30',
                Rule::unique('trainees', 'reg_no')->ignore($id),
            ],
            'electronic_reg_no' => [
                'nullable', 'string', 'max:30',
                Rule::unique('trainees', 'electronic_reg_no')->ignore($id),
            ],
            'last_name' => ['required', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name_fr' => ['nullable', 'string', 'max:100'],
            'first_name_fr' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['M', 'F'])],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'birth_place' => ['nullable', 'string', 'max:120'],
            'birth_wilaya' => ['nullable', 'string', 'max:120'],
            'residence_wilaya' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:20'],
            'father_name' => ['nullable', 'string', 'max:150'],
            'mother_last_name' => ['nullable', 'string', 'max:100'],
            'mother_name' => ['nullable', 'string', 'max:100'],
            'nin' => ['nullable', 'string', 'max:20'],
            'specialty' => ['nullable', 'string', 'max:150'],
            'diploma' => ['nullable', 'string', 'max:150'],
            'level' => ['nullable', 'string', 'max:100'],
            'training_mode' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'eligible' => ['nullable', 'boolean'],
            'file_accepted' => ['nullable', 'boolean'],
            'deliberation_accepted' => ['nullable', 'boolean'],
            'account_cancelled' => ['nullable', 'boolean'],
            'special_needs' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'registered_at' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function attributes(): array
    {
        return [
            'reg_no' => 'رقم التسجيل',
            'electronic_reg_no' => 'الرقم الإلكتروني',
            'last_name' => 'اللقب',
            'first_name' => 'الاسم',
            'birth_date' => 'تاريخ الميلاد',
            'phone' => 'الهاتف',
            'email' => 'البريد الإلكتروني',
            'nin' => 'رقم التعريف الوطني',
            'specialty' => 'الاختصاص',
            'diploma' => 'الشهادة',
            'level' => 'المستوى',
            'training_mode' => 'نمط التكوين',
            'start_date' => 'تاريخ البداية',
            'end_date' => 'تاريخ النهاية',
            'photo' => 'الصورة',
        ];
    }
}
