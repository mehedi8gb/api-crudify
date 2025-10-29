<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

use Mehedi8gb\ApiCrudify\Stubs\Base\BaseStub;

class CreateService extends BaseStub
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
        $className        = $this->modelBinding['className'];            // Original model class name, e.g., "Order"
        $classVar         = lcfirst($className);                         // e.g., "order"
        $classNamePlural  = $this->pluralize($className);                // e.g., "Orders"
        $classNameCamel   = $this->toCamelCase($classNamePlural);        // e.g., "orders" => "orders" (camelCase)
        $classNameTitle   = ucfirst($classNamePlural);                   // e.g., "Orders"
        $methodNameSuffix = ucfirst($classNameCamel);                    // e.g., "Orders" (used in getXXXData)
        $repositoryClass = "{$className}Repository";
        $repositoryObject = '$'."{$classVar}Repository";
        $modelNameSpace = $this->normalizeNamespaceToGetSingleDirectory($this->namespace);

        return "<?php

namespace App\Services\\{$this->namespace};

use App\Http\Resources\\{$this->namespace}\\{$className}\\{$className}Resource;
use App\Models\\{$modelNameSpace}\\{$className};
use App\Repositories\\{$this->namespace}\\{$repositoryClass};
use App\Services\V1\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Throwable;
use Exception;

/**
 * {$className} business logic service.
 *
 * Responsibilities:
 * - Orchestrate {$classVar} operations
 * - Enforce business rules
 * - Coordinate with repository
 *
 * Does NOT handle:
 * - HTTP requests (controller's job)
 * - Resource transformation (controller's job)
 * - Direct DB queries (repository's job)
 */
final class {$className}Service extends BaseService
{
    public function __construct(
        protected $repositoryClass $repositoryObject,
        protected Request \$request
    ) {
        parent::__construct($repositoryObject, \$request);
    }

    /**
     * Get {$classNameTitle} data.
     *
     * Returns data formatted for API response.
     * @throws Exception
     */
    public function get{$methodNameSuffix}Collection(): array
    {
         \$data = \$this->{$classVar}Repository->get{$methodNameSuffix}Data();

        return \$this->prepareResourceResponse(\$data, {$className}Resource::class);
    }

    /**
     * Create a new {$classVar} with business validation.
     *
     * @throws Throwable
     */
    public function create{$className}(array \$data): {$className}
    {
        return \$this->create(\$data);
    }

    /**
     * Update a {$classVar} with business validation.
     *
     * @throws Throwable
     */
    public function update{$className}({$className} \${$classVar}, array \$data): Model
    {
        return \$this->update(\${$classVar}, \$data);
    }

    /**
     * Delete a {$classVar} with business validation.
     *
     * @throws Throwable
     */
    public function delete{$className}({$className} \${$classVar}): bool
    {
        return \$this->delete(\${$classVar});
    }

    // ==========================================
    // Business Logic Hooks
    // ==========================================

    /**
     * Validate data before creation.
     */
    protected function beforeCreate(array \$data): array
    {
        // Business rule: Generate slug if not provided
        // if (empty(\$data['slug']) && !empty(\$data['name'])) {
        //     \$data['slug'] = \Str::slug(\$data['name']);
        // }

        return \$data;
    }

    /**
     * Post-creation operations.
     */
    protected function afterCreate(Model \$model, array \$rawData): void
    {
        // Example: Fire event, invalidate cache, log activity
        // event(new {$className}Created(\$model));
    }

    /**
     * Validate data before update.
     */
    protected function beforeUpdate(Model \$model, array \$data): array
    {
        // Business rule: Regenerate slug if name changed
        // if (isset(\$data['name']) && \$data['name'] !== \$model->name) {
        //     \$data['slug'] = \Str::slug(\$data['name']);
        // }

        return \$data;
    }

    /**
     * Post-update operations.
     */
    protected function afterUpdate(Model \$model): void
    {
        // event(new {$className}Updated(\$model));
    }

    /**
     * Validate before deletion.
     */
    protected function beforeDelete(Model \$model): void
    {
        // Business rule: Add any deletion constraints here
        // Example: Cannot delete if used by other entities
    }

    /**
     * Post-delete operations.
     */
    protected function afterDelete(Model \$model): void
    {
        // event(new {$className}Deleted(\$model));
    }

    public function findAll(array \$with = []): Collection
    {
        return \$this->{$classVar}Repository->all();
    }
}
        ";
    }
}
