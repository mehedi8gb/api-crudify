<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

use Mehedi8gb\ApiCrudify\Stubs\Base\BaseStub;

class CreateController extends BaseStub
{
    private array $modelBinding;
    private string $namespace;

    public function __construct(array $modelBinding, string $controllerPath)
    {
        $this->modelBinding = $modelBinding;
        $this->namespace = str_replace('/', '\\', $controllerPath);
    }

    public function generate(): string
    {
        $className = $this->modelBinding['className'];
        $classVar = lcfirst($className);
        $classNamePlural = $this->pluralize(strtolower($className));
        $classNamePluralTitle = ucfirst($classNamePlural);
        $serviceClass = "{$className}Service";
        $modelNameSpace = $this->normalizeNamespaceToGetSingleDirectory($this->namespace);

        return "<?php

namespace App\Http\Controllers\\{$this->namespace};

use App\Http\Controllers\V1\BaseController;
use App\Http\Requests\\{$this->namespace}\\{$className}\\{$className}StoreRequest;
use App\Http\Requests\\{$this->namespace}\\{$className}\\{$className}UpdateRequest;
use App\Http\Resources\\{$this->namespace}\\{$className}\\{$className}Resource;
use App\Models\\{$modelNameSpace}\\{$className};
use App\Services\\{$this->namespace}\\{$serviceClass};
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * {$className} HTTP controller.
 *
 * Responsibilities:
 * - Handle HTTP requests/responses
 * - Request validation (via FormRequests)
 * - Resource transformation
 * - Pagination/filtering
 *
 * Does NOT handle:
 * - Business logic (service's job)
 * - Database queries (repository's job)
 */
final class {$className}Controller extends BaseController
{
    public function __construct(
        private readonly {$serviceClass} \$service
    ) {}

    /**
     * List {$classNamePlural} with pagination/filtering.
     *
     * GET /api/v1/{$classNamePlural}
     * @throws Exception
     */
    public function index(): JsonResponse
    {
        // Get collection from service
        \$collection = \$this->service->get{$classNamePluralTitle}Collection();

        return \$this->successResponse('{$classNamePluralTitle} retrieved successfully', \$collection);
    }

    /**
     * Create a new {$classVar}.
     *
     * POST /api/v1/{$classNamePlural}
     * @throws Throwable
     */
    public function store({$className}StoreRequest \$request): JsonResponse
    {
        \${$classVar} = \$this->service->create{$className}(\$request->validated());

        return \$this->successResponse(
            '{$className} created successfully',
            new {$className}Resource(\${$classVar}),
            Response::HTTP_CREATED
        );
    }

    /**
     * Show a specific {$classVar}.
     *
     * GET /api/v1/{$classNamePlural}/{{$classVar}}
     */
    public function show({$className} \${$classVar}): JsonResponse
    {
        return \$this->successResponse(
            '{$className} retrieved successfully',
            new {$className}Resource(\${$classVar})
        );
    }

    /**
     * Update a {$classVar}.
     *
     * PUT/PATCH /api/v1/{$classNamePlural}/{{$classVar}}
     * @throws Throwable
     */
    public function update({$className}UpdateRequest \$request, {$className} \${$classVar}): JsonResponse
    {
        \$updated = \$this->service->update{$className}(\${$classVar}, \$request->validated());

        return \$this->successResponse(
            '{$className} updated successfully',
            new {$className}Resource(\$updated),
            Response::HTTP_ACCEPTED
        );
    }

    /**
     * Delete a {$classVar}.
     *
     * DELETE /api/v1/{$classNamePlural}/{{$classVar}}
     * @throws Throwable
     */
    public function destroy({$className} \${$classVar}): JsonResponse
    {
        \$this->service->delete{$className}(\${$classVar});

        return \$this->successResponse(
            '{$className} deleted successfully',
            null,
            Response::HTTP_NO_CONTENT
        );
    }
}
        ";
    }
}
