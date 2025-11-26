<?php

namespace App\Http\Requests\Api\V1\Transaction;

use App\Enums\TransactionCategory;
use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListTransactionRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'sometimes|nullable|string|max:255',
            'type' => ['sometimes', 'nullable', Rule::enum(TransactionType::class)],

            'category' => 'sometimes|nullable|array',
            'category.*' => ['string', Rule::enum(TransactionCategory::class)],

            'group_id' => 'sometimes|nullable|array',
            'group_id.*' => 'integer|exists:groups,id',

            'date_start' => 'sometimes|nullable|date',
            'date_end' => 'sometimes|nullable|date|after_or_equal:date_start',
        ];
    }
}
