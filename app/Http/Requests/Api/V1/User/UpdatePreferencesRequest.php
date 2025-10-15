<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'favorite_group_id' => [
                'nullable',
                'integer',
                'exists:groups,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $user = $this->user();
                        $group = \App\Models\Group::find($value);

                        if (!$group || !$group->members->contains($user)) {
                            $fail('Você só pode favoritar um grupo do qual você é membro.');
                        }
                    }
                },
            ],
        ];
    }
}
