<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

use Mehedi8gb\ApiCrudify\Stubs\Base\BaseStub;

class CreateSeeder extends BaseStub
{
    private array $modelBinding;
    private string $namespace;

    public function __construct(array $modelBinding, string $controllerPath)
    {
        $this->modelBinding = $modelBinding;
        $this->namespace = $this->normalizeNamespaceToGetSingleDirectory($controllerPath);
    }

    public function generate(): string
    {
        $className = $this->modelBinding['className'];

        return "<?php

namespace Database\\Seeders\\{$this->namespace};

use Illuminate\\Database\\Seeder;
use Database\\Factories\\{$this->namespace}\\{$className}Factory;

class {$className}Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        {$className}Factory::times(5)->create();
    }
}
";
    }
}
