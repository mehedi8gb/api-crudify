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
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'max:1200'],
        ];
    }

    public function authorize(): bool
    {
        return true;
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
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'max:1200'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
        ";
    }
}
