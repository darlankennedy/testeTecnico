<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('me');

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => "sometimes|required|email|unique:users,email,$userId",
            'cpf' => "sometimes|required|string|unique:users,cpf,$userId|max:14",
            'password' => 'nullable|string|min:6|confirmed',
        ];
    }
}
