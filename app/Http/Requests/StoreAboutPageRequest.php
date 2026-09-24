<?php

// app/Http/Requests/UpdateAboutPageRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAboutPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by can:manage-about route middleware
    }

    public function rules(): array
    {
        return [
            'story' => ['nullable', 'string', 'max:5000'],
            'mission' => ['nullable', 'string', 'max:2000'],
            'vision' => ['nullable', 'string', 'max:2000'],
            'hero_image' => ['nullable', 'image', 'max:5120'],
            'remove_hero_image' => ['boolean'],
        ];
    }
}
