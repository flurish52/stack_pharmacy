<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update-order-status');
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:processing,out_for_delivery,ready_for_pickup,completed',
        ];
    }
}
