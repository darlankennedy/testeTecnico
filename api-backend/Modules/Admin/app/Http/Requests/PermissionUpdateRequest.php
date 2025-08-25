<?php

namespace Modules\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => [
                'required','string','max:100',
                Rule::unique('permissions', 'name')->ignore($this->route('permission')->id ?? null),
            ],
        ];
    }
}
