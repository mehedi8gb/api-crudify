<?php

namespace Mehedi8gb\ApiCrudify\Commands;

use Mehedi8gb\ApiCrudify\Services\ComponentGeneratorService;
use Mehedi8gb\ApiCrudify\Services\FileSystemHelper;
use Mehedi8gb\ApiCrudify\Services\NamingHelper;
use Mehedi8gb\ApiCrudify\Services\RouteManagerService;

class ApiCrudifyCommand extends BaseCommand
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

    private ComponentGeneratorService $componentGenerator;
    private NamingHelper $namingHelper;
    private RouteManagerService $routeManager;

    public function handle(): void
    {
        $this->initializeServices();

        $fullPath = $this->argument('name');
        // Parse: V1/Inventory/Specification -> ['V1/Inventory', 'Specification']
        $pathParts = explode('/', $fullPath);
        $className = array_pop($pathParts);
        $domainPath = implode('/', $pathParts);

        $controllerName = $this->namingHelper->formatControllerName($className);
        $modelBinding = $this->namingHelper->getModelBinding($controllerName);

        $this->info("\n Generating CRUD for: {$className}");
        $this->info("📁 Domain Path: {$domainPath}");

        // Ensure base classes exist
        $this->restoreBaseClasses();

        // Create all components
        $this->hasFile['controller'] = $this->componentGenerator->generateController($modelBinding, $domainPath);
        $this->hasFile['model'] = $this->componentGenerator->generateModel($modelBinding, $domainPath);
        $this->hasFile['service'] = $this->componentGenerator->generateService($modelBinding, $domainPath);
        $this->hasFile['repository'] = $this->componentGenerator->generateRepository($modelBinding, $domainPath);
        $this->hasFile['formRequest'] = $this->componentGenerator->generateFormRequests($modelBinding, $domainPath);
        $this->hasFile['resource'] = $this->componentGenerator->generateResource($modelBinding, $domainPath);
        $this->hasFile['migration'] = $this->componentGenerator->generateMigration($modelBinding);
        $this->hasFile['test'] = $this->componentGenerator->generateFeatureTest($modelBinding, $domainPath);
        $this->hasFile['factory'] = $this->componentGenerator->generateFactory($modelBinding, $domainPath);
        $this->hasFile['seeder'] = $this->componentGenerator->generateSeederClass($modelBinding, $domainPath);

        $this->routeManager->updateRoutesFile($modelBinding, $domainPath, $controllerName);

        $this->displaySummary();
    }

    private function initializeServices(): void
    {
        $fileHelper = new FileSystemHelper();
        $this->namingHelper = new NamingHelper();
        $this->componentGenerator = new ComponentGeneratorService($fileHelper, $this);
        $this->routeManager = new RouteManagerService($fileHelper, $this);
    }

    private function displaySummary(): void
    {
        $allFilesExist = ! in_array(false, $this->hasFile, true);

        if ($allFilesExist) {
            $this->warn("\n⚠️  All files already exist.");
        } elseif (in_array(true, $this->hasFile, true)) {
            $this->info("\n✨ Some files created successfully.");
            $this->warn('⚠️  Some files already existed.');
        } else {
            $this->info("\n✨ CRUD created successfully!");
        }

        if ($this->option('export-api-schema')) {
            $this->call('optimize:clear');
            $this->call('export:postman');
        }
    }
}
