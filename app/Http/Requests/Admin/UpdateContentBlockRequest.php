<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'value' => ['nullable', 'string'],
            'type' => ['sometimes', 'string', Rule::in(['text', 'textarea', 'html', 'image', 'number', 'link'])],
            'meta' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
