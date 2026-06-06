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
            'meta.text_color' => ['sometimes', 'nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'meta.object_position_x' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'meta.object_position_y' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'meta.object_zoom' => ['sometimes', 'nullable', 'numeric', 'min:1', 'max:2'],
        ];
    }
}
