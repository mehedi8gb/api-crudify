<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

use Mehedi8gb\ApiCrudify\Stubs\Base\BaseStub;

class CreateFormRequest extends BaseStub
{
    private array $modelBinding;
    private string $namespace;

    public function __construct(array $modelBinding, string $domainPath)
    {
        $this->modelBinding = $modelBinding;
        $this->namespace = str_replace('/', '\\', $domainPath);
    }

    public function generateStore(): string
    {
        $className = $this->modelBinding['className'];

        return "<?php

namespace App\Http\Requests\\{$this->namespace}\\{$className};

use Illuminate\Foundation\Http\FormRequest;

class {$this->modelBinding['className']}StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
        ];
    }
}
        ";
    }

    public function generateUpdate(): string
    {
        $className = $this->modelBinding['className'];

        return "<?php

namespace App\Http\Requests\\{$this->namespace}\\{$className};

use Illuminate\Foundation\Http\FormRequest;

class {$className}UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
        ];
    }
}
        ";
    }
}
