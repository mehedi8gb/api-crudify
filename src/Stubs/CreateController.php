<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

class CreateController
{
    private array $modelBinding;
    private mixed $controllerPath;

    public function __construct(array $modelBinding, string $controllerPath)
    {
        $this->modelBinding = $modelBinding;
        if ($controllerPath === '') {
            $this->controllerPath = '';
        } else {
            $this->controllerPath = '\\' . str_replace('/', '\\', $controllerPath);
        }
    }

    public function generate(): string
    {
        $className = $this->modelBinding['className'];
        $classNameLower = strtolower($className);
        $classNamePlural = $this->pluralize($classNameLower);
        $classNamePluralTitle = ucfirst($classNamePlural);
        $serviceClass = "{$className}Service";
        $serviceVar = lcfirst($className);

        return "<?php

namespace App\Http\Controllers\V1$this->controllerPath;

use App\Http\Requests\\{$className}\\{$className}StoreRequest;
use App\Http\Requests\\{$className}\\{$className}UpdateRequest;
use App\Http\Resources\\{$className}\\{$className}Resource;
use App\Services\V1$this->controllerPath\\{$serviceClass};
use Exception;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class {$className}Controller extends Controller
{
    public function __construct(private readonly {$serviceClass} \$service) {}

    /**
     * @throws Exception
     */
    public function index()
    {
        \${$classNamePlural} = \$this->service->list{$classNamePluralTitle}();
        return sendSuccessResponse(
            '{$classNamePluralTitle} retrieved successfully',
            \${$classNamePlural},
            Response::HTTP_OK
        );
    }

    /**
     * @throws Throwable
     */
    public function store({$className}StoreRequest \$request)
    {
        \${$serviceVar} = \$this->service->create{$className}(\$request->validated());

        return sendSuccessResponse(
            '{$className} created successfully',
            new {$className}Resource(\${$serviceVar}),
            Response::HTTP_CREATED
        );
    }

    public function show(string \${$serviceVar}Id)
    {
        \${$serviceVar} = \$this->service->get{$className}(\${$serviceVar}Id);
        if (! \${$serviceVar}) {
            return sendErrorResponse('{$className} not found', Response::HTTP_NOT_FOUND);
        }

        return sendSuccessResponse(
            '{$className} retrieved successfully',
            new {$className}Resource(\${$serviceVar}),
            Response::HTTP_OK
        );
    }

    public function update({$className}UpdateRequest \$request, string \${$serviceVar}Id)
    {
        try {
            \${$serviceVar} = \$this->service->update{$className}(\${$serviceVar}Id, \$request->validated());

            return sendSuccessResponse(
                '{$className} updated successfully',
                new {$className}Resource(\${$serviceVar}),
                Response::HTTP_OK
            );
        } catch (Exception \$e) {
            return sendErrorResponse(\$e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function destroy(string \${$serviceVar}Id)
    {
        try {
            \$this->service->delete{$className}(\${$serviceVar}Id);
            return sendSuccessResponse('{$className} deleted successfully', null, Response::HTTP_NO_CONTENT);
        } catch (Exception \$e) {
            return sendErrorResponse(\$e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
        ";
    }

    private function pluralize(string $word): string
    {
        if (str_ends_with($word, 'y')) {
            return substr($word, 0, -1) . 'ies';
        } elseif (in_array(substr($word, -1), ['s', 'x', 'z']) || in_array(substr($word, -2), ['ch', 'sh'])) {
            return $word . 'es';
        } else {
            return $word . 's';
        }
    }
}
