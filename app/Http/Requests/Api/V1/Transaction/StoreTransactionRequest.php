<?php

namespace App\Http\Requests\Api\V1\Transaction;

use App\Enums\TransactionCategory;
use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_id' => 'nullable|exists:groups,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'type' => ['required', Rule::enum(TransactionType::class)],
            'category' => ['required', Rule::enum(TransactionCategory::class)],
            'transaction_date' => 'required|date',
        ];
    }
}
