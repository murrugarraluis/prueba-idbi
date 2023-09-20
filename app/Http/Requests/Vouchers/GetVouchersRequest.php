<?php

namespace App\Http\Requests\Vouchers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetVouchersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page' => ['required', 'int', 'gt:0'],
            'paginate' => ['required', 'int', 'gt:0'],
            'serie' => ['nullable', 'string'],
            'number' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => [Rule::requiredIf(function () {
                return $this->query('start_date') != null;
            }), 'date_format:Y-m-d'],
        ];
    }
}
