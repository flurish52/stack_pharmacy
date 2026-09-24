<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('manage-categories');
    }

    protected function prepareForValidation(): void
    {
        // Blank slug -> build one from the name.
        $this->merge([
//            'slug' => Str::slug($this->input('slug') ?: (string) $this->input('name')),
        ]);
    }

    public function rules(): array
    {
        $category = $this->route('category'); // null on create

        return [
            'name' => ['required', 'string', 'max:255'],
//            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category?->id)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['boolean'],
        ];
    }
}
