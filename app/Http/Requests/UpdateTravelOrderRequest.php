<?php

namespace App\Http\Requests;

use App\Enums\TravelOrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTravelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:'.implode(',', [
                TravelOrderStatusEnum::APPROVED->value,
                TravelOrderStatusEnum::CANCELED->value,
            ]),
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'The status is required.',
            'status.string' => 'The status must be a string.',
            'status.in' => 'The status must be one of the following: '.
                TravelOrderStatusEnum::APPROVED->value.' or '.TravelOrderStatusEnum::CANCELED->value,
        ];
    }
}
