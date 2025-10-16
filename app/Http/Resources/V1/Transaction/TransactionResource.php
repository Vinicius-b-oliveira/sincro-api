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

            'amount' => number_format($this->amount, 2, '.', ''),

            'type' => $this->type->value,

            'transaction_date' => $this->transaction_date->toIso8601String(),

            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
