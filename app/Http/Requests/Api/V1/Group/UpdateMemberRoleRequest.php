<?php

namespace App\Http\Requests\Api\V1\Group;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => [
                'required',
                'string',
                Rule::in(['admin', 'member']),
            ],
        ];
    }
}
