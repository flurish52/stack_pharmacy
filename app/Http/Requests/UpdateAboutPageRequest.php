<?php

// app/Http/Requests/UpdateAboutPageRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAboutPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'story' => ['nullable', 'string', 'max:5000'],
            'mission' => ['nullable', 'string', 'max:2000'],
            'vision' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:5120'],        // was hero_image
            'remove_image' => ['boolean'],                        // was remove_hero_image
        ];
    }
}
