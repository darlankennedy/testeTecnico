<?php

namespace Modules\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'                  => ['required','string','max:255'],
            'email'                 => ['required','string','email','unique:users,email'],
            'cpf'                   => ['required','string','cpf','unique:users,cpf'],
            'password'              => ['required','string','min:6','confirmed'],
            'password_confirmation'  => ['required','string','min:6'],
        ];
    }
}
