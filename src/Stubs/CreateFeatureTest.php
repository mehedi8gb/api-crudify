<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

use Mehedi8gb\ApiCrudify\Stubs\Base\BaseStub;

class CreateFeatureTest extends BaseStub
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
        $factoryClassName = $className . 'Factory';
        $classVar = lcfirst($className);
        $classNamePlural = $this->pluralize(strtolower($className));
        $modelNameSpace = $this->normalizeNamespaceToGetSingleDirectory($this->namespace);

        return "<?php

namespace Tests\Feature\\{$this->namespace};

use Database\Factories\\{$modelNameSpace}\\{$factoryClassName};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class {$className}Test extends TestCase
{
    use RefreshDatabase;

    /**
     * Test listing {$classNamePlural}.
     */
    public function test_can_list_{$classNamePlural}(): void
    {
        {$factoryClassName}::times(3)->create();

        \$response = \$this->getJson('/api/v1/{$classNamePlural}');

        \$response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'slug']
                ]
            ]);
    }

    /**
     * Test creating a {$classVar}.
     */
    public function test_can_create_{$classVar}(): void
    {
        \$data = [
            'name' => 'Test {$className}',
            'slug' => 'test-{$classVar}',
        ];

        \$response = \$this->postJson('/api/v1/{$classNamePlural}', \$data);

        \$response->assertStatus(201)
            ->assertJsonFragment([
                'message' => '{$className} created successfully'
            ]);

        \$this->assertDatabaseHas('{$classNamePlural}', [
            'name' => 'Test {$className}',
            'slug' => 'test-{$classVar}',
        ]);
    }

    /**
     * Test showing a specific {$classVar}.
     */
    public function test_can_show_{$classVar}(): void
    {
        \${$classVar} = {$factoryClassName}::new()->create();

        \$response = \$this->getJson('/api/v1/{$classNamePlural}/' . \${$classVar}->id);

        \$response->assertStatus(200)
            ->assertJsonFragment([
                'name' => \${$classVar}->name,
                'slug' => \${$classVar}->slug,
            ]);
    }

    /**
     * Test updating a {$classVar}.
     */
    public function test_can_update_{$classVar}(): void
    {
        \${$classVar} = {$factoryClassName}::new()->create();

        \$data = [
            'name' => 'Updated {$className}',
        ];

        \$response = \$this->putJson('/api/v1/{$classNamePlural}/' . \${$classVar}->id, \$data);

        \$response->assertStatus(202)
            ->assertJsonFragment([
                'message' => '{$className} updated successfully'
            ]);

        \$this->assertDatabaseHas('{$classNamePlural}', [
            'id' => \${$classVar}->id,
            'name' => 'Updated {$className}',
        ]);
    }

    /**
     * Test deleting a {$classVar}.
     */
    public function test_can_delete_{$classVar}(): void
    {
        \${$classVar} = {$factoryClassName}::new()->create();

        \$response = \$this->deleteJson('/api/v1/{$classNamePlural}/' . \${$classVar}->id);

        \$response->assertStatus(204);

        \$this->assertDatabaseMissing('{$classNamePlural}', [
            'id' => \${$classVar}->id,
        ]);
    }

    /**
     * Test validation errors.
     */
    public function test_validation_errors_when_creating_{$classVar}(): void
    {
        \$response = \$this->postJson('/api/v1/{$classNamePlural}', []);

        \$response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
      ";
    }
}
