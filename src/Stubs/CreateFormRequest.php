<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

class CreateFormRequest
{

    private array $modelBinding;

    public function __construct(array $modelBinding)
    {
        $this->modelBinding = $modelBinding;
    }

    public function generateStore(): string
    {
        return "<?php

namespace App\Http\Requests\\{$this->modelBinding['className']};

use Illuminate\Foundation\Http\FormRequest;

class {$this->modelBinding['className']}StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Authorization logic goes here (e.g., check if the user has permission)
    }

    protected function prepareForValidation()
    {
        // You can perform data manipulation here
        \$this->merge([
            'title' => ucwords(strtolower(\$this->input('title'))), // Convert name to title case
            // Add more data manipulation as needed
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            // 'image' => 'required|image|mimes:jpeg,png|max:2048', // Example validation rules
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            // 'image.uploaded' => 'The image must be a valid JPEG or PNG file.',
            // Define custom validation error messages here
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'product title',
            'description' => 'product description',
            // 'image' => 'product image',
            // Define custom attribute names for validation error messages here
        ];
    }

    protected function passedValidation(): void
    {
        // You can further manipulate the validated data here
        // For example, you can hash the password before storing it in the database
        \$this->merge([
        // 'password' => bcrypt(\$this->input('password')),
        ]);
    }
}
        ";
    }

    public function generateUpdate(): string
    {
        return "<?php

namespace App\Http\Requests\\{$this->modelBinding['className']};

use Illuminate\Foundation\Http\FormRequest;

class {$this->modelBinding['className']}UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Authorization logic goes here (e.g., check if the user has permission)
    }

    protected function prepareForValidation()
    {
        // You can perform data manipulation here
        \$this->merge([
            'title' => ucwords(strtolower(\$this->input('title'))), // Convert name to title case
            // Add more data manipulation as needed
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            // 'image' => 'required|image|mimes:jpeg,png|max:2048', // Example validation rules
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            // 'image.uploaded' => 'The image must be a valid JPEG or PNG file.',
            // Define custom validation error messages here
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'product title',
            'description' => 'product description',
            // 'image' => 'product image',
            // Define custom attribute names for validation error messages here
        ];
    }

    protected function passedValidation(): void
    {
        // You can further manipulate the validated data here
        // For example, you can hash the password before storing it in the database
        \$this->merge([
        // 'password' => bcrypt(\$this->input('password')),
        ]);
    }
}
        ";
    }
}
