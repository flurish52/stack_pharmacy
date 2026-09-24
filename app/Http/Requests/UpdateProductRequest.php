<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization happens in the controller via ProductPolicy.
        return true;
    }

    protected function prepareForValidation(): void
    {

    }

    public function rules(): array
    {
        $product = $this->route('product'); // null on create

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            // unique() also checks soft-deleted rows, which is what we want:
            // the DB unique index still covers them.
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['active', 'draft', 'archived'])],

            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', Rule::exists('categories', 'id')],
            'primary_category_id' => ['required', 'integer', Rule::in((array) $this->input('category_ids', []))],

            // A product with zero variants is an invalid state.
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.variant_name' => ['required', 'string', 'max:255'],
            'variants.*.price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'variants.*.stock_quantity' => ['required', 'integer', 'min:0'],
        ];

        foreach ((array) $this->input('variants', []) as $i => $variant) {
            $id = is_array($variant) ? ($variant['id'] ?? null) : null;

            $rules["variants.$i.sku"] = [
                'nullable', 'string', 'max:100', 'distinct',
                Rule::unique('product_variants', 'sku')->ignore($id),
            ];

            // An existing variant id must belong to THIS product.
            if ($id) {
                $rules["variants.$i.id"][] = Rule::exists('product_variants', 'id')
                    ->where('product_id', $product?->id);
            }
        }

        return $rules;
    }

    public function productData(): array
    {
        return $this->safe()->only(['name', 'slug', 'description', 'status']);
    }
}
