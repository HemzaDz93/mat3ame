<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin', 'supervisor', 'staff') === true;
    }

    public function rules(): array
    {
        return [
            'trainee_id' => ['required', 'integer', Rule::exists('trainees', 'id')->whereNull('deleted_at')],
            'plan' => ['required', Rule::in(array_keys(Payment::plans()))],
            'start_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'trainee_id' => 'المتربص',
            'plan' => 'نوع السداد',
            'start_date' => 'تاريخ البداية',
            'note' => 'ملاحظة',
        ];
    }
}
