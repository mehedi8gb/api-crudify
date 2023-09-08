<?php

namespace Mehedi8gb\ApiCrudify\Commands;

use Mehedi8gb\ApiCrudify\Stubs\CreateController;
use Illuminate\Console\Command;
use Mehedi8gb\ApiCrudify\Stubs\CreateFactory;
use Mehedi8gb\ApiCrudify\Stubs\CreateFormRequest;
use Mehedi8gb\ApiCrudify\Stubs\CreateMigration;
use Mehedi8gb\ApiCrudify\Stubs\CreateModel;
use Mehedi8gb\ApiCrudify\Stubs\CreateResource;
use Illuminate\Support\Str;


class ApiCrudifyCommand extends Command
{

    protected $signature = 'crudify:make {name} {--export-api-schema}';
    protected $description = 'Create a new CRUD controller with related components';
    protected array $hasFile = [
        'controller' => false,
        'model' => false,
        'formRequest' => false,
        'resource' => false,
        'resourceCollection' => false,
        'migration' => false,
        'factory' => false,
        'seeder' => false,
    ];

    public function handle(): void
    {
        $controllerName = $this->argument('name');
        $controllerNameWithoutSuffix = $this->formatControllerName($controllerName);
        $controllerPath = str_replace('/', '', $this->getControllerPath($controllerName));
        $modelBinding = $this->getModelBinding($controllerNameWithoutSuffix);
        $controllerFileName = str_replace('//|\\\\', "/", app_path("Http/Controllers/api/{$controllerPath}/{$controllerNameWithoutSuffix}.php"));

        $this->createDirectoryIfNotExists($controllerFileName);
        $this->hasFile['controller'] = $this->isFileExists($controllerFileName);

        if (!$this->hasFile['controller']) {
            $controllerContent = (new CreateController($modelBinding, $controllerPath))->generate();
            file_put_contents($controllerFileName, $controllerContent);
            $this->info("\nController created: <fg=yellow>{$controllerFileName}</>");
        } else {
            $this->info("\nController already exists: <fg=red>{$controllerFileName}</>");
        }

        // Create and save model
        $this->generateAndSaveModel($modelBinding);

        // Create and save form request classes
        $this->generateAndSaveFormRequests($modelBinding);

        // Create and save resource and resource collection
        $this->generateAndSaveResource($modelBinding);

        // Update routes file
        $this->updateRoutesFile($modelBinding, $controllerPath, $controllerNameWithoutSuffix);

        // Create and save migration
        $this->generateAndSaveMigration($modelBinding);

        // Create and save factory
        $this->generateAndSaveFactory($modelBinding);

        // create seeder class
        $this->generateSeederClass($modelBinding);

        $allFilesExist = array_reduce($this->hasFile, fn($carry, $value) => $carry && $value, true);

        if ($allFilesExist) {
            $this->info("\nAll those files already exist.");
        } else if (in_array(true, $this->hasFile, true)) {
            $this->info("\n<fg=bright-yellow>Some of those files already exist.</>");
            $this->info("and some of those files created successfully.");
        } else {
            $this->info("\nCRUD created successfully!");
            $exportApiSchema = $this->option('export-api-schema');
            if ($exportApiSchema) {
                $this->call('optimize:clear');
                $this->call('export:postman');
            }
        }
    }

    private function createDirectoryIfNotExists(string $controllerFileName): void
    {
        if (!is_dir(dirname($controllerFileName))) {
            mkdir(dirname($controllerFileName), 0755, true);
        }
    }

    private function isFileExists(string $controllerFileName): bool
    {
        if (file_exists($controllerFileName)
        ) return true;
        return false;
    }

    private function generateAndSaveModel(array $modelBinding): void
    {
        $modelFileName = app_path("Models/{$modelBinding['className']}.php");
        $this->hasFile['model'] = $this->isFileExists($modelFileName);
        if (!$this->hasFile['model']) {
            $modelContent = (new CreateModel($modelBinding))->generate();
            file_put_contents($modelFileName, $modelContent);
            $this->info("\nModel created: <fg=yellow>{$modelFileName}</>");
        } else {
            $this->info("\nModel already exists: <fg=red>{$modelFileName}</>");
        }
    }

    private function generateAndSaveFormRequests(array $modelBinding): void
    {
        $formRequestDirectory = app_path("Http/Requests/{$modelBinding['className']}");
        $formRequestStoreFileName = "{$formRequestDirectory}/{$modelBinding['className']}StoreRequest.php";
        $formRequestUpdateFileName = "{$formRequestDirectory}/{$modelBinding['className']}UpdateRequest.php";

        $this->createDirectoryIfNotExists($formRequestStoreFileName);
        $this->hasFile['formRequest'] = $this->isFileExists($formRequestStoreFileName);
        if (!$this->hasFile['formRequest']) {
            $formRequestStoreContent = (new CreateFormRequest($modelBinding))->generateStore();
            file_put_contents($formRequestStoreFileName, $formRequestStoreContent);
            $this->info("\nStore Form requests created: <fg=yellow>{$formRequestStoreFileName}</>");
        } else {
            $this->info("\nStore Form requests already exists: <fg=red>{$formRequestStoreFileName}</>");
        }
        $this->hasFile['formRequest'] = $this->isFileExists($formRequestUpdateFileName);
        if (!$this->hasFile['formRequest']) {
            $formRequestUpdateContent = (new CreateFormRequest($modelBinding))->generateUpdate();
            file_put_contents($formRequestUpdateFileName, $formRequestUpdateContent);
            $this->info("\nUpdate Form requests created: <fg=yellow>{$formRequestUpdateFileName}</>");
        } else {
            $this->info("\nUpdate Form requests already exists: <fg=red>{$formRequestUpdateFileName}</>");
        }
    }

    private function generateAndSaveResource(array $modelBinding): void
    {
        $resourceDirectory = app_path("Http/Resources/{$modelBinding['className']}");
        $resourceFileName = "{$resourceDirectory}/{$modelBinding['className']}Resource.php";
        $resourceCollectionFileName = "{$resourceDirectory}/{$modelBinding['className']}ResourceCollection.php";

        $this->createDirectoryIfNotExists($resourceFileName);
        $this->hasFile['resource'] = $this->isFileExists($resourceFileName);
        if (!$this->hasFile['resource']) {
            $resourceContent = (new CreateResource($modelBinding))->generateResource();
            file_put_contents($resourceFileName, $resourceContent);
            $this->info("\nResource created: <fg=yellow>{$resourceFileName}</>");
        } else {
            $this->info("\nResource already exists: <fg=red>{$resourceFileName}</>");
        }

        $this->hasFile['resourceCollection'] = $this->isFileExists($resourceCollectionFileName);
        if (!$this->hasFile['resourceCollection']) {
            $resourceCollectionContent = (new CreateResource($modelBinding))->generateResourceCollection();
            file_put_contents($resourceCollectionFileName, $resourceCollectionContent);
            $this->info("\nResource collection created: <fg=yellow>{$resourceCollectionFileName}</>");
        } else {
            $this->info("\nResource collection already exists: <fg=red>{$resourceCollectionFileName}</>");
        }
    }

    private function updateRoutesFile(array $modelBinding, string $controllerPath, string $controllerNameWithoutSuffix): void
    {
        $routeFileName = base_path('routes/api.php');
        // create a new route file

        $routeName = Str::kebab($modelBinding['className']);
        $this->addApiResourceRoute($routeFileName, $routeName, $controllerPath, "{$controllerNameWithoutSuffix}");
        $result = $this->addUseStatementToRoutesFile($routeFileName, "use App\Http\Controllers\api\\{$controllerPath}\\{$controllerNameWithoutSuffix};");
        if ($result) {
            $this->info("\nRoute added: <fg=yellow>{$routeFileName}</>");
        } else {
            $this->info("\nRoute already exists: <fg=red>{$routeFileName}</>");
        }
    }

    private function generateAndSaveMigration(array $modelBinding): void
    {
        $modelBinding['className'] = strtolower($modelBinding['className']) . 's';
        $migrationFileName = database_path("migrations/" . date('Y_m_d_His') . "_create_{$modelBinding['className']}_table.php");
        $substringToMatch = "_create_{$modelBinding['className']}_table.php";
        $files = scandir(database_path("migrations"));
        foreach ($files as $file) {
            if (str_contains($file, $substringToMatch)) {
                $filePath = database_path("migrations/{$file}");

                if (file_exists($filePath)) {
                    $this->hasFile['migration'] = true;
                }
            }
        }
        if (!$this->hasFile['migration']) {
            $migrationContent = (new CreateMigration($modelBinding))->generate();
            file_put_contents($migrationFileName, $migrationContent);
            $this->info("\nMigration created: <fg=yellow>{$migrationFileName}</>");
        } else {
            $this->info("\nMigration already exists: <fg=red>{$migrationFileName}</>");
        }
    }

    private function generateAndSaveFactory(array $modelBinding): void
    {
        $factoryFileName = database_path("factories/{$modelBinding['className']}Factory.php");
        $this->hasFile['factory'] = $this->isFileExists($factoryFileName);
        if (!$this->hasFile['factory']) {
            $factoryContent = (new CreateFactory($modelBinding))->generate();
            file_put_contents($factoryFileName, $factoryContent);
            $this->info("\nFactory created: <fg=yellow>{$factoryFileName}</>");
        } else {
            $this->info("\nFactory already exists: <fg=red>{$factoryFileName}</>");
        }
    }

    private function generateSeederClass(array $modelBinding): void
    {
        $databaseSeederFileName = database_path("seeders/DatabaseSeeder.php");

        $databaseSeederContent = file_get_contents($databaseSeederFileName);

        if (!str_contains($databaseSeederContent, "use App\Models\\{$modelBinding['className']}")) {
            $databaseSeederContent = preg_replace(
                '/namespace Database\\\\Seeders;/',
                "namespace Database\\Seeders;\n\nuse App\Models\\{$modelBinding['className']}; ",
                $databaseSeederContent,
                1
            );
            file_put_contents($databaseSeederFileName, $databaseSeederContent);
        } else {
            $this->info("\nSeeder already exists in the DatabaseSeeder class: <fg=red>{$databaseSeederFileName}</>");
        }

        if (!str_contains($databaseSeederContent, "{$modelBinding['className']}::factory()->count(10)->create();")) {
            $databaseSeederContent = preg_replace('/public\s+function\s+run\s*\(\s*\): void\s*{/',
                "public function run(): void\n    {\n        {$modelBinding['className']}::factory()->count(10)->create();",
                $databaseSeederContent,
                1
            );
            file_put_contents($databaseSeederFileName, $databaseSeederContent);
            $this->info("\nSeeder added to the DatabaseSeeder class: <fg=yellow>{$databaseSeederFileName}</>");
        } else {
            $this->info("\nSeeder already exists in the DatabaseSeeder class: <fg=red>{$databaseSeederFileName}</>");
        }

    }

    // helper functions start here

    private
    function formatControllerName(string $name): string
    {
        $segments = explode('/', $name);
        $controllerName = end($segments);

        return str_contains($controllerName, 'Controller') ? $controllerName : $controllerName . 'Controller';
    }

    private
    function getControllerPath(string $name): string
    {
        $segments = explode('/', $name);
        array_pop($segments);
        if (count($segments) == 0) return '';
        return '/' . implode('/', $segments);
    }

    private
    function getModelBinding(string $controllerName): array
    {
        $array['className'] = str_replace('Controller', '', $controllerName);
        $array['classVar'] = '$' . Str::lower(str_replace('Controller', '', $controllerName));
        return $array;
    }

    private
    function addApiResourceRoute(string $routeFileName, string $routeName, string $controllerPath, string $controllerClass): void
    {
        $routeContent = file_get_contents($routeFileName);
        if (
            str_contains($routeContent, "Route::prefix('{$routeName}')->group(function () {")
        ) return;

        $routeContent .= "\nRoute::prefix('{$routeName}')->group(function () {\n
            Route::get('/', [{$controllerClass}::class, 'index'])->name('{$routeName}.index');\n
            Route::get('show/{slug}', [{$controllerClass}::class, 'show'])->name('{$routeName}.show');\n
            Route::post('store/{{$routeName}}', [{$controllerClass}::class, 'store'])->name('{$routeName}.store');\n
            Route::put('update/{{$routeName}}', [{$controllerClass}::class, 'update'])->name('{$routeName}.update');\n
            Route::delete('destroy/{{$routeName}}', [{$controllerClass}::class, 'destroy'])->name('{$routeName}.destroy');\n
        });\n";
        file_put_contents($routeFileName, $routeContent);
    }

    private
    function addUseStatementToRoutesFile(string $routeFileName, string $useStatement): bool
    {
        $routeContent = file_get_contents($routeFileName);
        $routeUseStatement = str_replace('//|\\\\', '/', $useStatement);

        // Check if the use statement already exists
        if (!str_contains($routeContent, $routeUseStatement)) {
            // Insert the use statement at the top of the file
            $routeContent = preg_replace('/<\?php/', "<?php\n{$routeUseStatement}", $routeContent, 1);
            return file_put_contents($routeFileName, $routeContent);
        }
        return false;
    }


}

