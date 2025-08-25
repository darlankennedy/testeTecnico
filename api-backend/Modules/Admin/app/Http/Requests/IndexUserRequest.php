<?php

namespace Modules\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Admin\DTO\UsersIndexDto;

class IndexUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $sortWhitelist = ['id','name','email','cpf'];
        $withWhitelist = [''];

        return [
            'per_page'  => ['integer','min:1','max:100'],
            'search'    => ['nullable','string','max:255'],
            'order_by'  => ['sometimes','in:'.implode(',', $sortWhitelist)],
            'direction' => ['sometimes','in:asc,desc'],
            'with'      => ['array'],
            'with.*'    => ['in:'.implode(',', $withWhitelist)],
            'filters.status'     => ['nullable','in:active,inactive,pending'],
            'filters.role'       => ['nullable','string','max:64'],
            'filters.active'     => ['nullable','boolean'],
            'filters.created_at' => ['nullable','array'],
            'filters.created_at.from' => ['nullable','date'],
            'filters.created_at.to'   => ['nullable','date','after_or_equal:filters.created_at.from'],
        ];
    }
    public function toDto(): UsersIndexDto
    {
        return new UsersIndexDto(
            perPage: (int) $this->integer('per_page', 15),
            search: $this->string('search'),
            orderBy: $this->input('order_by', 'id'),
            direction: $this->input('direction', 'asc'),
            with: $this->input('with', []),
            filters: [
                'status'       => $this->input('filters.status'),
                'role'         => $this->input('filters.role'),
                'active'       => $this->boolean('filters.active', true),
                'created_from' => $this->input('filters.created_at.from'),
                'created_to'   => $this->input('filters.created_at.to'),
            ]
        );
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
