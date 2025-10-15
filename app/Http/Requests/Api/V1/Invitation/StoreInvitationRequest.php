<?php

namespace App\Http\Requests\Api\V1\Invitation;

use App\Enums\InvitationStatus;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvitationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    $group = $this->route('group');

                    if ($group->members()->where('email', $value)->exists()) {
                        $fail('Este usuário já é um membro do grupo.');
                    }
                },
                function ($attribute, $value, $fail) {
                    $group = $this->route('group');

                    if ($group->invitations()->where('email', $value)->where('status', InvitationStatus::PENDING)->exists()) {
                        $fail('Já existe um convite pendente para este e-mail neste grupo.');
                    }
                },
            ],
        ];
    }
}
