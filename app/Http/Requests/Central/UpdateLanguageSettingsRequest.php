<?php

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLanguageSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'languages' => ['nullable', 'array'],
            'languages.*.code' => ['required', 'string', 'max:10'],
            'languages.*.name' => ['required', 'string', 'max:255'],
            'languages.*.native' => ['nullable', 'string', 'max:255'],
            'languages.*.enabled' => ['boolean'],
        ];
    }
}
