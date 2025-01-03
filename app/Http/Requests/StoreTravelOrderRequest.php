<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTravelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'applicant_name' => 'required|string|max:255',
            'applicant_email' => 'required|email',
            'destination' => 'required|string|max:255',
            'departure_date' => 'required|date|after_or_equal:today',
            'return_date' => 'required|date|after_or_equal:departure_date',
        ];
    }

    public function messages(): array
    {
        return [
            'applicant_name.required' => 'The applicant name is required.',
            'applicant_name.string' => 'The applicant name must be a string.',
            'applicant_name.max' => 'The applicant name may not be greater than 255 characters.',
            'applicant_email.required' => 'The applicant email is required.',
            'applicant_email.email' => 'The applicant email must be a valid email address.',
            'destination.required' => 'The destination is required.',
            'destination.string' => 'The destination must be a string.',
            'destination.max' => 'The destination may not be greater than 255 characters.',
            'departure_date.required' => 'The departure date is required.',
            'departure_date.date' => 'The departure date must be a date.',
            'departure_date.after_or_equal' => 'The departure date must be a date after or equal to today.',
            'return_date.required' => 'The return date is required.',
            'return_date.date' => 'The return date must be a date.',
            'return_date.after_or_equal' => 'The return date must be a date after or equal to departure date.',
        ];
    }
}
