<?php

namespace App\Http\Requests\Api\V1\Analytics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(['3m', '6m', '1y']),
                'required_without_all:start_date,end_date',

                'prohibits:start_date,end_date',
            ],
            'start_date' => [
                'sometimes',
                'nullable',
                'date',
                'required_with:end_date',
                'required_without:period',

                'prohibits:period',
            ],
            'end_date' => [
                'sometimes',
                'nullable',
                'date',
                'after_or_equal:start_date',
                'required_with:start_date',
                'required_without:period',

                'prohibits:period',
            ],

            'group_id' => 'sometimes|nullable|integer|exists:groups,id',
            'view_mode' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(['individual', 'group']),
            ],
        ];
    }
}
