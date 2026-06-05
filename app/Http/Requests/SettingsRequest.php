<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'base_currency' => ['required', 'string', 'size:3'],
            'language' => ['required', 'in:en,ru,de'],
            'theme' => ['required', 'in:light,dark,system'],
            'notification_preferences' => ['nullable', 'array'],
        ];
    }
}
