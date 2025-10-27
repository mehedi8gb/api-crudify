<?php

namespace Mehedi8gb\ApiCrudify\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Mehedi8gb\ApiCrudify\Stubs\Base\BaseStub;
use Mehedi8gb\ApiCrudify\Stubs\CreateController;
use Mehedi8gb\ApiCrudify\Stubs\CreateFactory;
use Mehedi8gb\ApiCrudify\Stubs\CreateFeatureTest;
use Mehedi8gb\ApiCrudify\Stubs\CreateFormRequest;
use Mehedi8gb\ApiCrudify\Stubs\CreateMigration;
use Mehedi8gb\ApiCrudify\Stubs\CreateModel;
use Mehedi8gb\ApiCrudify\Stubs\CreateRepository;
use Mehedi8gb\ApiCrudify\Stubs\CreateResource;
use Mehedi8gb\ApiCrudify\Stubs\CreateSeeder;
use Mehedi8gb\ApiCrudify\Stubs\CreateService;

class ApiCrudifyCommand extends Command
{
    protected $signature = 'crudify:make {name} {--export-api-schema}';
    protected $description = 'Create a new CRUD with Service-Repository pattern';
    protected array $hasFile = [
        'controller' => false,
        'model' => false,
        'formRequest' => false,
        'resource' => false,
        'migration' => false,
        'factory' => false,
        'seeder' => false,
        'service' => false,
        'repository' => false,
        'test' => false,
    ];

    public function handle(): void
    {
        $fullPath = $this->argument('name');

        // Parse: V1/Inventory/Specification -> ['V1/Inventory', 'Specification']
        $pathParts = explode('/', $fullPath);
        $className = array_pop($pathParts);
        $domainPath = implode('/', $pathParts);

        $controllerName = $this->formatControllerName($className);
        $modelBinding = $this->getModelBinding($controllerName);

        $this->info("\n Generating CRUD for: {$className}");
        $this->info("📁 Domain Path: {$domainPath}");

        // Create all components
        $this->generateController($modelBinding, $domainPath);
        $this->generateModel($modelBinding, $domainPath);
        $this->generateService($modelBinding, $domainPath);
        $this->generateRepository($modelBinding, $domainPath);
        $this->generateFormRequests($modelBinding, $domainPath);
        $this->generateResource($modelBinding, $domainPath);
        $this->generateMigration($modelBinding);
        $this->generateFeatureTest($modelBinding, $domainPath);
        $this->generateFactory($modelBinding, $domainPath);
        $this->generateSeederClass($modelBinding, $domainPath);
        $this->updateRoutesFile($modelBinding, $domainPath, $controllerName);

        $this->displaySummary();
    }

    private function generateController(array $modelBinding, string $domainPath): void
    {
        $fileName = app_path("Http/Controllers/{$domainPath}/{$modelBinding['className']}Controller.php");
        $this->createDirectoryIfNotExists($fileName);

        if ($this->isFileExists($fileName)) {
            $this->hasFile['controller'] = true;
            $this->warn("Controller already exists: {$fileName}");
            return;
        }

        $content = (new CreateController($modelBinding, $domainPath))->generate();
        file_put_contents($fileName, $content);
        $this->info("✓ Controller created: {$fileName}");
    }

    private function generateModel(array $modelBinding, string $domainPath): void
    {
        // Normalize and sanitize the namespace
        $dir = BaseStub::normalizeNamespaceSanitize($domainPath);

        // Safely build the model path
        $modelDir = $dir ? "Models/{$dir}" : "Models";
        $fileName = app_path("{$modelDir}/{$modelBinding['className']}.php");
        $this->createDirectoryIfNotExists($fileName);

        if ($this->isFileExists($fileName)) {
            $this->hasFile['model'] = true;
            $this->warn("Model already exists: {$fileName}");
            return;
        }

        $content = (new CreateModel($modelBinding, $domainPath))->generate();
        file_put_contents($fileName, $content);
        $this->info("✓ Model created: {$fileName}");
    }

    private function generateService(array $modelBinding, string $domainPath): void
    {
        $fileName = app_path("Services/{$domainPath}/{$modelBinding['className']}Service.php");
        $this->createDirectoryIfNotExists($fileName);

        if ($this->isFileExists($fileName)) {
            $this->hasFile['service'] = true;
            $this->warn("Service already exists: {$fileName}");
            return;
        }

        $content = (new CreateService($modelBinding, $domainPath))->generate();
        file_put_contents($fileName, $content);
        $this->info("✓ Service created: {$fileName}");
    }

    private function generateRepository(array $modelBinding, string $domainPath): void
    {
        $fileName = app_path("Repositories/{$domainPath}/{$modelBinding['className']}Repository.php");
        $this->createDirectoryIfNotExists($fileName);

        if ($this->isFileExists($fileName)) {
            $this->hasFile['repository'] = true;
            $this->warn("Repository already exists: {$fileName}");
            return;
        }

        $content = (new CreateRepository($modelBinding, $domainPath))->generate();
        file_put_contents($fileName, $content);
        $this->info("✓ Repository created: {$fileName}");
    }

    private function generateFormRequests(array $modelBinding, string $domainPath): void
    {
        $directory = app_path("Http/Requests/{$domainPath}/{$modelBinding['className']}");
        $storeFile = "{$directory}/{$modelBinding['className']}StoreRequest.php";
        $updateFile = "{$directory}/{$modelBinding['className']}UpdateRequest.php";

        $this->createDirectoryIfNotExists($storeFile);

        if (!$this->isFileExists($storeFile)) {
            $content = (new CreateFormRequest($modelBinding, $domainPath))->generateStore();
            file_put_contents($storeFile, $content);
            $this->info("✓ Store Request created: {$storeFile}");
        } else {
            $this->hasFile['formRequest'] = true;
            $this->warn("Store Request already exists: {$storeFile}");
        }

        if (!$this->isFileExists($updateFile)) {
            $content = (new CreateFormRequest($modelBinding, $domainPath))->generateUpdate();
            file_put_contents($updateFile, $content);
            $this->info("✓ Update Request created: {$updateFile}");
        } else {
            $this->warn("Update Request already exists: {$updateFile}");
        }
    }

    private function generateResource(array $modelBinding, string $domainPath): void
    {
        $directory = app_path("Http/Resources/{$domainPath}/{$modelBinding['className']}");
        $fileName = "{$directory}/{$modelBinding['className']}Resource.php";

        $this->createDirectoryIfNotExists($fileName);

        if ($this->isFileExists($fileName)) {
            $this->hasFile['resource'] = true;
            $this->warn("Resource already exists: {$fileName}");
            return;
        }

        $content = (new CreateResource($modelBinding, $domainPath))->generate();
        file_put_contents($fileName, $content);
        $this->info("✓ Resource created: {$fileName}");
    }

    private function generateMigration(array $modelBinding): void
    {
        $tableName = Str::snake(Str::pluralStudly($modelBinding['className']));
        $migrationFileName = database_path("migrations/" . date('Y_m_d_His') . "_create_{$tableName}_table.php");
        $substringToMatch = "_create_{$tableName}_table.php";

        $files = scandir(database_path("migrations"));
        foreach ($files as $file) {
            if (str_contains($file, $substringToMatch)) {
                $this->hasFile['migration'] = true;
                $this->warn("Migration already exists: {$file}");
                return;
            }
        }

        $content = (new CreateMigration($modelBinding))->generate();
        file_put_contents($migrationFileName, $content);
        $this->info("✓ Migration created: {$migrationFileName}");
    }

    private function generateFactory(array $modelBinding, string $domainPath): void
    {
        $dir = BaseStub::normalizeNamespaceSanitize($domainPath);
        $factoryDir = $dir ? "factories/{$dir}" : "factories";
        $fileName = database_path("{$factoryDir}/{$modelBinding['className']}Factory.php");
        $this->createDirectoryIfNotExists($fileName);

        if ($this->isFileExists($fileName)) {
            $this->hasFile['factory'] = true;
            $this->warn("Factory already exists: {$fileName}");
            return;
        }

        $content = (new CreateFactory($modelBinding, $dir))->generate();
        file_put_contents($fileName, $content);
        $this->info("✓ Factory created: {$fileName}");
    }

    private function generateFeatureTest(array $modelBinding, string $domainPath): void
    {
        $fileName = base_path("tests/Feature/{$domainPath}/{$modelBinding['className']}Test.php");
        $this->createDirectoryIfNotExists($fileName);

        if ($this->isFileExists($fileName)) {
            $this->hasFile['test'] = true;
            $this->warn("Test already exists: {$fileName}");
            return;
        }

        $content = (new CreateFeatureTest($modelBinding, $domainPath))->generate();
        file_put_contents($fileName, $content);
        $this->info("✓ Feature Test created: {$fileName}");
    }

//    private function generateSeederClass(array $modelBinding): void
//    {
//        $seederFile = database_path("seeders/DatabaseSeeder.php");
//        $seederContent = file_get_contents($seederFile);
//        $className = $modelBinding['className'];
//
//        if (!str_contains($seederContent, "use App\Models\\{$className}")) {
//            $seederContent = preg_replace(
//                '/namespace Database\\\\Seeders;/',
//                "namespace Database\\Seeders;\n\nuse App\Models\\{$className};",
//                $seederContent,
//                1
//            );
//        }
//
//        if (!str_contains($seederContent, "{$className}::factory()->count(10)->create();")) {
//            $seederContent = preg_replace(
//                '/public\s+function\s+run\s*\(\s*\): void\s*{/',
//                "public function run(): void\n    {\n        {$className}::factory()->count(10)->create();",
//                $seederContent,
//                1
//            );
//            file_put_contents($seederFile, $seederContent);
//            $this->info("✓ Seeder added to DatabaseSeeder");
//        } else {
//            $this->warn("Seeder already exists in DatabaseSeeder");
//        }
//    }

    private function generateSeederClass(array $modelBinding, $domainPath): void
    {
        // Normalize and sanitize the namespace
        $dir = BaseStub::normalizeNamespaceSanitize($domainPath);

        // Safely build the seeder path
        $seederDir = $dir ? "seeders/{$dir}" : "seeders";
        $fileName = database_path("{$seederDir}/{$modelBinding['className']}Seeder.php");
        $this->createDirectoryIfNotExists($fileName);

        // 1️⃣ Create Seeder File if Not Exists
        if ($this->isFileExists($fileName)) {
            $this->hasFile['seeder'] = true;
            $this->warn("Seeder already exists: {$fileName}");
        } else {
            $content = (new CreateSeeder($modelBinding, $domainPath))->generate();
            file_put_contents($fileName, $content);
            $this->info("✓ Seeder created: {$fileName}");
        }

        // 2️⃣ Now Register it Inside DatabaseSeeder.php
        $databaseSeederFile = database_path("seeders/DatabaseSeeder.php");

        if (!file_exists($databaseSeederFile)) {
            // If missing, create the base DatabaseSeeder file
            $baseSeederContent = <<<PHP
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\\{$modelBinding['className']}Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        \$this->call([
            {$modelBinding['className']}Seeder::class,
        ]);
    }
}
PHP;
            file_put_contents($databaseSeederFile, $baseSeederContent);
            $this->info("✓ Created new DatabaseSeeder and registered {$modelBinding['className']}Seeder");
            return;
        }

        // Read current content
        $content = file_get_contents($databaseSeederFile);
        $seederClass = "{$modelBinding['className']}Seeder";
        $seederClassWithDomain = ($dir ? $dir . '\\' : '') . $seederClass;

        // Add `use` statement if not exists
        if (!str_contains($content, "use Database\\Seeders\\{$seederClassWithDomain};")) {
            $content = preg_replace(
                '/namespace Database\\\\Seeders;/',
                "namespace Database\\Seeders;\n\nuse Database\\Seeders\\{$seederClassWithDomain};",
                $content,
                1
            );
        }

        // If run() already contains a $this->call([...])
        if (preg_match('/\$this->call\s*\(\s*\[(.*?)]\s*\)\s*;/s', $content, $matches)) {
            $existing = trim($matches[1]);

            if (!str_contains($existing, $seederClass)) {
                // Rebuild $this->call([...]) with proper formatting
                $allSeeders = array_filter(array_map(function($line) {
                    return rtrim(trim($line), ','); // remove spaces + trailing comma
                }, explode("\n", $existing)));
                $allSeeders[] = "{$seederClass}::class";

                // Proper indentation and newlines
                $formattedSeeders = "            " . implode(",\n            ", $allSeeders) . ",";
                $newCallBlock = "\n" . $formattedSeeders . "\n        "; // opening and closing brackets indentation

                // Replace old block
                $content = str_replace($matches[1], $newCallBlock, $content);
                file_put_contents($databaseSeederFile, $content);
                $this->info("✓ Registered {$seederClass} in DatabaseSeeder");
            } else {
                $this->warn("Seeder {$seederClass} already registered in DatabaseSeeder");
            }
        } else {
            // No $this->call block — create one
            $runMethod = <<<PHP

    public function run(): void
    {
        \$this->call([
            {$seederClass}::class,
        ]);
    }
PHP;
            $content = preg_replace(
                '/class\s+DatabaseSeeder\s+extends\s+Seeder\s*\{/',
                "class DatabaseSeeder extends Seeder\n{{$runMethod}",
                $content,
                1
            );
            file_put_contents($databaseSeederFile, $content);
            $this->info("✓ Added run() method and registered {$seederClass}");
        }
    }
    private function updateRoutesFile(array $modelBinding, string $domainPath, string $controllerName): void
    {
        $routeFile = base_path('routes/api.php');
        $routeName = Str::kebab($modelBinding['className']);
        $namespace = str_replace('/', '\\', $domainPath);

        $this->addApiResourceRoute($routeFile, $routeName, $controllerName);
        $this->addUseStatement($routeFile, "use App\Http\Controllers\\{$namespace}\\{$controllerName};");
    }

    private function addApiResourceRoute(string $routeFile, string $routeName, string $controllerClass): void
    {
        $routeContent = file_get_contents($routeFile);
        $prefix = Str::of($domainPath ?? '')->lower()->replace('\\', '/');
        $prefix = $prefix->isNotEmpty() ? "{$prefix}/" : '';

        if (str_contains($routeContent, "Route::apiResource('{$prefix}{$routeName}', {$controllerClass}::class);")) {
            $this->warn("Route already exists for: {$routeName}");
            return;
        }

//        $routeContent .= "\nRoute::prefix('{$routeName}')->group(function () {
//    Route::get('/', [{$controllerClass}::class, 'index'])->name('{$routeName}.index');
//    Route::get('show/{" . Str::singular($routeName) . "}', [{$controllerClass}::class, 'show'])->name('{$routeName}.show');
//    Route::post('store', [{$controllerClass}::class, 'store'])->name('{$routeName}.store');
//    Route::put('update/{" . Str::singular($routeName) . "}', [{$controllerClass}::class, 'update'])->name('{$routeName}.update');
//    Route::delete('destroy/{" . Str::singular($routeName) . "}', [{$controllerClass}::class, 'destroy'])->name('{$routeName}.destroy');
//});\n";

        $routeContent .= "\nRoute::apiResource('{$prefix}{$routeName}', {$controllerClass}::class);\n";

        file_put_contents($routeFile, $routeContent);
        $this->info("✓ Routes added for: {$routeName}");
    }

    private function addUseStatement(string $routeFile, string $useStatement): void
    {
        $routeContent = file_get_contents($routeFile);

        if (str_contains($routeContent, $useStatement)) {
            return;
        }

        $routeContent = preg_replace(
            '/<\?php/',
            "<?php\n{$useStatement}",
            $routeContent,
            1
        );

        file_put_contents($routeFile, $routeContent);
    }

    private function displaySummary(): void
    {
        $allFilesExist = !in_array(false, $this->hasFile, true);

        if ($allFilesExist) {
            $this->warn("\n⚠️  All files already exist.");
        } elseif (in_array(true, $this->hasFile, true)) {
            $this->info("\n✨ Some files created successfully.");
            $this->warn("⚠️  Some files already existed.");
        } else {
            $this->info("\n✨ CRUD created successfully!");
        }

        if ($this->option('export-api-schema')) {
            $this->call('optimize:clear');
            $this->call('export:postman');
        }
    }

    // Helper methods
    private function createDirectoryIfNotExists(string $fileName): void
    {
        $directory = dirname($fileName);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    private function isFileExists(string $fileName): bool
    {
        return file_exists($fileName);
    }

    private function formatControllerName(string $name): string
    {
        return str_contains($name, 'Controller') ? $name : $name . 'Controller';
    }

    private function getModelBinding(string $controllerName): array
    {
        $className = str_replace('Controller', '', $controllerName);
        return [
            'className' => $className,
            'classVar' => ' '. Str::lower($className),
        ];
    }
}
