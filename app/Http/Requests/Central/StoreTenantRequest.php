<?php

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\TenantStatus;

class StoreTenantRequest extends FormRequest
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
            'id' => [
                'required',
                'string',
                'max:255',
                'unique:tenants,id',
                'regex:/^[a-z0-9-]+$/',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:tenants,email',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'plan_id' => [
                'nullable',
                'exists:plans,id',
            ],
            'status' => [
                'sometimes',
                Rule::enum(TenantStatus::class),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.required' => 'ID tenant обязателен для заполнения.',
            'id.unique' => 'Tenant с таким ID уже существует.',
            'id.regex' => 'ID может содержать только строчные буквы, цифры и дефисы.',
            'email.required' => 'Email обязателен для заполнения.',
            'email.unique' => 'Tenant с таким email уже существует.',
        ];
    }
}
