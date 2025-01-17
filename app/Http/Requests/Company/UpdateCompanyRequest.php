<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:companies,name'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:companies,email'],
            'phone' => ['required', 'string', 'max:12', 'unique:companies,phone']
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    private function message(): array{
        return [
            'name.required' => 'Name is required',
            'name.unique' => 'Name is already taken',
            'email.required' => 'Email is required',
            'email.email' => 'Email is invalid',
            'email.unique' => 'Email is already taken',
            'phone.required' => 'Phone is required',
            'phone.unique' => 'Phone is already taken',
        ];
    }
}
