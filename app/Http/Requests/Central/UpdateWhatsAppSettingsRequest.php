<?php

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWhatsAppSettingsRequest extends FormRequest
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
            'whatsapp_enabled' => ['boolean'],
            'whatsapp_api_key' => ['nullable', 'string', 'max:255'],
            'whatsapp_api_secret' => ['nullable', 'string', 'max:255'],
            'whatsapp_phone_number' => ['nullable', 'string', 'max:50'],
            'whatsapp_business_id' => ['nullable', 'string', 'max:255'],
            'whatsapp_webhook_url' => ['nullable', 'url', 'max:500'],
        ];
    }
}
