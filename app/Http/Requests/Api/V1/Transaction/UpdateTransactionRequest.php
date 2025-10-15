<?php

namespace App\Http\Requests\Api\V1\Transaction;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_id' => 'sometimes|nullable|exists:groups,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'type' => ['sometimes', 'required', Rule::enum(TransactionType::class)],
            'transaction_date' => 'sometimes|required|date',
        ];
    }
}
