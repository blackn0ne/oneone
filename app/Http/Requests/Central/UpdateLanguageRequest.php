<?php

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLanguageRequest extends FormRequest
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
        $languageId = $this->route('language')->id ?? $this->route('language');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('languages', 'code')->ignore($languageId),
                'regex:/^[a-z]{2}(-[A-Z]{2})?$/',
            ],
            'is_active' => [
                'boolean',
            ],
            'sort_order' => [
                'integer',
                'min:0',
            ],
        ];
    }
}
