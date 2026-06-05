<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WatchlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'ticker' => ['required', 'string', 'max:24'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'exchange' => ['nullable', 'string', 'max:64'],
            'currency' => ['required', 'string', 'size:3'],
        ];
    }
}
