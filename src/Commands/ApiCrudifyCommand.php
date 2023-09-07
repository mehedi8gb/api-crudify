<?php

namespace Mehedi8gb\ApiCrudify\Commands;

use Mehedi8gb\ApiCrudify\Stubs\CreateController;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Console\Command;
use Mehedi8gb\ApiCrudify\Stubs\CreateFactory;
use Mehedi8gb\ApiCrudify\Stubs\CreateFormRequest;
use Mehedi8gb\ApiCrudify\Stubs\CreateMigration;
use Mehedi8gb\ApiCrudify\Stubs\CreateModel;
use Mehedi8gb\ApiCrudify\Stubs\CreateResource;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


class ApiCrudifyCommand extends Command
{

    protected $signature = 'crudify:make {name}';
    protected $description = 'Create a new CRUD controller with related components';

    public function handle(): void
    {
        $controllerName = $this->argument('name');
        $controllerNameWithoutSuffix = $this->formatControllerName($controllerName);
        $controllerPath = $this->getControllerPath($controllerName);
        $modelBinding = $this->getModelBinding($controllerNameWithoutSuffix);
        $controllerFileName = app_path("Http/Controllers/api{$controllerPath}/{$controllerNameWithoutSuffix}.php");

        $this->createDirectoryIfNotExists($controllerFileName);
        $this->deleteExistingFile($controllerFileName);

        $controllerContent = (new CreateController($modelBinding, $controllerPath))->generate();
        file_put_contents($controllerFileName, $controllerContent);
        $this->info("\nController created: <fg=yellow>{$controllerFileName}</>");

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

        $this->info("\nCRUD created successfully!");
    }

    private function createDirectoryIfNotExists(string $controllerFileName): void
    {
        if (!is_dir(dirname($controllerFileName))) {
            mkdir(dirname($controllerFileName), 0755, true);
        }
    }

    private function deleteExistingFile(string $controllerFileName): void
    {
        if (file_exists($controllerFileName)) {
            File::delete($controllerFileName);
        }
    }

    private function generateAndSaveModel(array $modelBinding): void
    {
        $modelFileName = app_path("Models/{$modelBinding['className']}.php");
        $modelContent = (new CreateModel($modelBinding))->generate();

        file_put_contents($modelFileName, $modelContent);

        $this->info("\nModel created: <fg=yellow>{$modelFileName}</>");
    }

    private function generateAndSaveFormRequests(array $modelBinding): void
    {
        $formRequestDirectory = app_path("Http/Requests/{$modelBinding['className']}");
        $formRequestStoreFileName = "{$formRequestDirectory}/{$modelBinding['className']}StoreRequest.php";
        $formRequestUpdateFileName = "{$formRequestDirectory}/{$modelBinding['className']}UpdateRequest.php";

        $this->createDirectoryIfNotExists($formRequestStoreFileName);
        $this->deleteExistingFile($formRequestStoreFileName);

        $formRequestStoreContent = (new CreateFormRequest($modelBinding))->generateStore();
        $formRequestUpdateContent = (new CreateFormRequest($modelBinding))->generateUpdate();

        file_put_contents($formRequestStoreFileName, $formRequestStoreContent);
        file_put_contents($formRequestUpdateFileName, $formRequestUpdateContent);
        $this->info("\nStore Form requests created: <fg=yellow>{$formRequestStoreFileName}</>");
        $this->info("\nUpdate Form requests created: <fg=yellow>{$formRequestUpdateFileName}</>");
    }

    private function generateAndSaveResource(array $modelBinding): void
    {
        $resourceDirectory = app_path("Http/Resources/{$modelBinding['className']}");
        $resourceFileName = "{$resourceDirectory}/{$modelBinding['className']}Resource.php";
        $resourceCollectionFileName = "{$resourceDirectory}/{$modelBinding['className']}ResourceCollection.php";

        $this->createDirectoryIfNotExists($resourceFileName);
        $this->deleteExistingFile($resourceFileName);

        $resourceContent = (new CreateResource($modelBinding))->generateResource();
        $resourceCollectionContent = (new CreateResource($modelBinding))->generateResourceCollection();

        file_put_contents($resourceFileName, $resourceContent);
        file_put_contents($resourceCollectionFileName, $resourceCollectionContent);

        $this->info("\nResource created: <fg=yellow>{$resourceFileName}</>");
        $this->info("\nResource collection created: <fg=yellow>{$resourceCollectionFileName}</>");
    }

    private function updateRoutesFile(array $modelBinding, string $controllerPath, string $controllerNameWithoutSuffix): void
    {
        $routeFileName = base_path('routes/api.php');
        $routeName = Str::kebab($modelBinding['className']);
        $this->addApiResourceRoute($routeFileName, $routeName, "{$controllerNameWithoutSuffix}");
        $this->addUseStatementToRoutesFile($routeFileName, "use App\Http\Controllers\api\\{$controllerPath}\\{$controllerNameWithoutSuffix};");

        $this->info("\nRoute added: <fg=yellow>{$routeFileName}</>");
    }

    private function generateAndSaveMigration(array $modelBinding): void
    {
        $migrationFileName = database_path("migrations/" . date('Y_m_d_His') . "_create_{$modelBinding['className']}_table.php");
        $migrationContent = (new CreateMigration($modelBinding))->generate();
        $this->deleteExistingFile($migrationFileName);
        file_put_contents($migrationFileName, $migrationContent);
        $this->info("\nMigration created: <fg=yellow>{$migrationFileName}</>");
    }

    private function generateAndSaveFactory(array $modelBinding): void
    {
        $factoryFileName = database_path("factories/{$modelBinding['className']}Factory.php");
        $factoryContent = (new CreateFactory($modelBinding))->generate();

        $this->deleteExistingFile($factoryFileName);

        file_put_contents($factoryFileName, $factoryContent);
        $this->info("\nFactory created: <fg=yellow>{$factoryFileName}</>");

        $databaseSeederFileName = database_path("seeders/DatabaseSeeder.php");

        $databaseSeederContent = file_get_contents($databaseSeederFileName);

        if (!str_contains($databaseSeederContent, "use App\Models\\{$modelBinding['className']}")) {
            $databaseSeederContent = preg_replace(
                '/namespace Database\\\\Seeders;/',
                "namespace Database\\Seeders;\n\nuse App\Models\\{$modelBinding['className']};",
                $databaseSeederContent,
                1
            );
        }

        if (!str_contains($databaseSeederContent, "{$modelBinding['className']}::factory()")) {
            $databaseSeederContent = str_replace('//', "{$modelBinding['className']}::factory()->count(10)->create();", $databaseSeederContent);
        }
        file_put_contents($databaseSeederFileName, $databaseSeederContent);

        $this->info("\nFactory created: <fg=yellow>{$factoryFileName}</>");
    }

    private function formatControllerName(string $name): string
    {
        $segments = explode('/', $name);
        $controllerName = end($segments);

        return str_contains($controllerName, 'Controller') ? $controllerName : $controllerName . 'Controller';
    }

    private function getControllerPath(string $name): string
    {
        $segments = explode('/', $name);
        array_pop($segments);
        if (count($segments) == 0) return '';
        return '/'.implode('/', $segments);
    }

    private function getModelBinding(string $controllerName): array
    {
        $array['className'] = str_replace('Controller', '', $controllerName);
        $array['classVar'] = '$' . Str::lower(str_replace('Controller', '', $controllerName));
        return $array;
    }

    private function addApiResourceRoute(string $routeFileName, string $routeName, string $controllerClass): void
    {
        $routeContent = file_get_contents($routeFileName);
        $routeContent .= "\nRoute::apiResource('{$routeName}', {$controllerClass}::class);";
        file_put_contents($routeFileName, $routeContent);
    }

    private function addUseStatementToRoutesFile(string $routeFileName, string $useStatement): void
    {
        $routeContent = file_get_contents($routeFileName);

        // Check if the use statement already exists
        if (!str_contains($routeContent, $useStatement)) {
            // Insert the use statement at the top of the file
            $routeContent = preg_replace('/<\?php/', "<?php\n{$useStatement}", $routeContent, 1);
            file_put_contents($routeFileName, $routeContent);
        }
    }
}

