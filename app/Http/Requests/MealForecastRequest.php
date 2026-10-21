<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MealForecastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageMealForecast() === true;
    }

    public function rules(): array
    {
        return [
            'day' => ['required', 'date'],
            'expected_count' => ['required', 'integer', 'min:0', 'max:100000'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'day' => 'التاريخ',
            'expected_count' => 'العدد المتوقّع',
            'note' => 'ملاحظة',
        ];
    }
}
