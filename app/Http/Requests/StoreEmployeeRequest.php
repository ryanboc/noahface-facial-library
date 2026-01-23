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
            
            // Critical: This ID must be unique to map correctly to NoahFace events
            'noahface_id' => ['required', 'string', Rule::unique('employees')->ignore($employeeId)],
            
            'award_id' => 'required|exists:awards,id',
            
            'employment_type' => 'required|string|in:Casual,Full Time/Part Time', 
        ];
    }
}