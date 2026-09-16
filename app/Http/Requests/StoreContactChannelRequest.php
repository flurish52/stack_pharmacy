<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-contact');
    }

    public function rules(): array
    {
        return [
            'platform' => 'required|string|max:50',
            'handle' => 'required|string|max:255',
            'url' => 'nullable|url|max:255',
            'display_order' => 'integer|min:0',
        ];
    }
}
