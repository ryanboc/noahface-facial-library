<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Get the ID for unique checks (if updating)
        $employeeId = $this->route('employee') ? $this->route('employee')->id : null;

        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('employees')->ignore($employeeId)],
            'phone' => ['nullable', 'regex:/^\+[1-9]\d{7,14}$/'],
            'base_rate' => 'required|numeric|min:0',
            'noahface_id' => ['required', 'string', Rule::unique('employees')->ignore($employeeId)],

            'award_id' => 'required|exists:awards,id',

            'employment_type' => 'required|string|in:Casual,Full Time/Part Time',
            'annual_leave_allowance' => 'required|integer|min:0|max:365',
            'account_role' => ['nullable', Rule::in(['employee', 'manager', 'executive'])],
            'company_ids' => ['sometimes', 'array'],
            'company_ids.*' => ['integer', 'distinct', 'exists:companies,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter the mobile number in international format, for example +61412345678.',
        ];
    }
}
