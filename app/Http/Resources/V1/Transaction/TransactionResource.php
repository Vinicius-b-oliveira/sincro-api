<?php

namespace App\Http\Resources\V1\Transaction;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Transaction */
class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,

            'amount' => (float) $this->amount,

            'type' => $this->type->value,

            'category' => $this->category,

            'transaction_date' => $this->transaction_date->toIso8601String(),

            'created_at' => $this->created_at->toIso8601String(),

            'user_id' => $this->user_id,
            'user_name' => $this->user->name,

            'group_id' => $this->group_id,

            'group_name' => $this->group?->name,
        ];
    }
}
