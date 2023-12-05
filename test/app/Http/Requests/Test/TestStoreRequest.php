<?php

namespace App\Http\Requests\Test;

use Illuminate\Foundation\Http\FormRequest;

class TestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization logic goes here (e.g., check if the user has permission)
    }

    protected function prepareForValidation(): void
        {
            // You can perform data manipulation here
            $this->merge([
                'title' => ucwords(strtolower($this->input('title'))), // Convert name to title case
                // Add more data manipulation as needed
            ]);
        }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable',
            // 'image' => 'required|image|mimes:jpeg,png|max:2048', // Example validation rules
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            // Define custom validation error messages here
        ];
    }

    public function attributes(): array
    {
        return [
            //'title' => 'Post title',
            // Define custom attribute names for validation error messages here
        ];
    }

    protected function passedValidation(): void
    {
        // You can further manipulate the validated data after it has been inserted into the database
        // For example, you can hash the password before storing it in the database
        $this->replace([
            //
        ]);
    }
}
        