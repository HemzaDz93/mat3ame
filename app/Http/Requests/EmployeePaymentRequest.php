<?php

namespace App\Http\Requests;

use App\Models\EmployeePayment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin', 'supervisor', 'staff') === true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')->whereNull('deleted_at')],
            'plan' => ['required', Rule::in(array_keys(EmployeePayment::plans()))],
            'start_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'employee_id' => 'الموظف',
            'plan' => 'نوع السداد',
            'start_date' => 'تاريخ البداية',
            'note' => 'ملاحظة',
        ];
    }
}
