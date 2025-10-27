<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

use Mehedi8gb\ApiCrudify\Stubs\Base\BaseStub;

class CreateFactory extends BaseStub
{
    private array $modelBinding;
    private string $namespace;

    public function __construct(array $modelBinding, string $domainPath)
    {
        $this->modelBinding = $modelBinding;
        $this->namespace = str_replace('/', '\\', $domainPath);
    }

    public function generate(): string
    {
        $className = $this->modelBinding['className'];

        return "<?php

namespace Database\Factories\\{$this->namespace};

use App\Models\\{$this->namespace}\\{$className};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<{$className}>
 */
class {$className}Factory extends Factory
{
    protected \$model = {$className}::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'status' => fake()->boolean(80),
        ];
    }
}
        ";
    }
}
