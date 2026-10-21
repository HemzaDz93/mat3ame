<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin', 'supervisor', 'staff') === true;
    }

    public function rules(): array
    {
        $employee = $this->route('employee');
        $id = $employee instanceof Employee ? $employee->id : null;

        return [
            'emp_no' => [
                'nullable', 'string', 'max:30',
                Rule::unique('employees', 'emp_no')->ignore($id),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'birth_place' => ['nullable', 'string', 'max:120'],
            'hire_date' => ['nullable', 'date'],
            'rank' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function attributes(): array
    {
        return [
            'emp_no' => 'رقم الموظف',
            'first_name' => 'الاسم',
            'last_name' => 'اللقب',
            'birth_date' => 'تاريخ الميلاد',
            'birth_place' => 'مكان الميلاد',
            'hire_date' => 'تاريخ التوظيف',
            'rank' => 'الرتبة',
            'notes' => 'ملاحظات',
            'photo' => 'الصورة',
        ];
    }
}
