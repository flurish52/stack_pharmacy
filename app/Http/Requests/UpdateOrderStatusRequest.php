<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization happens in the controller via OrderPolicy.
        return true;
    }

    public function rules(): array
    {
        return [
            // paid / received / cancelled can never be set from here.
            // Whether this specific transition is valid for the order's
            // current state is checked in the controller, under a row lock.
            'status' => ['required', 'string', Rule::in(Order::SETTABLE_STATUSES)],
        ];
    }
}
