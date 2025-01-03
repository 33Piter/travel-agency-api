<?php

namespace App\Http\Requests;

use App\Enums\TravelOrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class IndexTravelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'departure_date_start' => ['nullable', 'date_format:Y-m-d'],
            'departure_date_end' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:departure_date_start'],
            'return_date_start' => ['nullable', 'date_format:Y-m-d'],
            'return_date_end' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:return_date_start'],
            'date_range_start' => ['nullable', 'date_format:Y-m-d'],
            'date_range_end' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_range_start'],
            'status' => ['nullable', 'string', 'in:'.implode(',', array_column(TravelOrderStatusEnum::cases(), 'value'))],
            'destination' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'departure_date_start.date_format' => 'The departure date start must be in the format YYYY-MM-DD.',
            'departure_date_end.after_or_equal' => 'The departure date end must be a date after or equal to departure date start.',
            'return_date_start.date_format' => 'The return date start must be in the format YYYY-MM-DD.',
            'return_date_end.after_or_equal' => 'The return date end must be a date after or equal to return date start.',
            'date_range_start.date_format' => 'The date range start must be in the format YYYY-MM-DD.',
            'date_range_end.after_or_equal' => 'The date range end must be a date after or equal to date range start.',
            'status.string' => 'The status must be a string.',
            'status.in' => 'The status must be one of the following values: '.
                implode(', ', array_column(TravelOrderStatusEnum::cases(), 'value')).'.',
            'destination.string' => 'The destination must be a string.',
        ];
    }
}
