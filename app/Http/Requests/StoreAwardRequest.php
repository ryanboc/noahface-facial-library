<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAwardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            // 1. Main Award Data
            'name' => 'required|string|max:255',
            'pay_guide_link' => 'nullable|url',

            // 2. Conditions (One-to-One)
            'conditions' => 'required|array',
            'conditions.hours_per_day_rule' => 'nullable|string',
            'conditions.leading_hand_allowance' => 'nullable|string',
            'conditions.meal_allowance' => 'nullable|string',
            'conditions.paid_break_rule' => 'nullable|string',
            'conditions.unpaid_break_rule' => 'nullable|string',
            'conditions.remarks' => 'nullable|string',

            // 3. Rates (One-to-Many)
            'rates' => 'present|array', // Allow empty array if no rates yet
            'rates.*.employment_type' => 'required|string|in:Casual,Full Time/Part Time',
            'rates.*.category' => 'required|string', // e.g., 'Overtime', 'Public Holiday'
            'rates.*.rate_value' => 'required|string', // e.g., '150%', '200%'
        ];
    }
}