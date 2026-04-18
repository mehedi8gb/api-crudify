<?php

namespace Mehedi8gb\ApiCrudify\Services;

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

class ComponentGeneratorService
{
    private FileSystemHelper $fileHelper;
    private $output;

    public function __construct(FileSystemHelper $fileHelper, $output = null)
    {
        $this->fileHelper = $fileHelper;
        $this->output = $output;
    }

    public function generateController(array $modelBinding, string $domainPath): bool
    {
        $fileName = app_path("Http/Controllers/{$domainPath}/{$modelBinding['className']}Controller.php");
        $this->fileHelper->createDirectoryIfNotExists($fileName);

        if ($this->fileHelper->isFileExists($fileName)) {
            $this->output?->warn("Controller already exists: {$fileName}");
            return true;
        }

        $content = (new CreateController($modelBinding, $domainPath))->generate();
        file_put_contents($fileName, $content);
        $this->output?->info("✓ Controller created: {$fileName}");
        return false;
    }

    public function generateModel(array $modelBinding, string $domainPath): bool
    {
        $dir = BaseStub::normalizeNamespaceSanitize($domainPath);
        $modelDir = $dir ? "Models/{$dir}" : 'Models';
        $fileName = app_path("{$modelDir}/{$modelBinding['className']}.php");
        $this->fileHelper->createDirectoryIfNotExists($fileName);

        if ($this->fileHelper->isFileExists($fileName)) {
            $this->output?->warn("Model already exists: {$fileName}");
            return true;
        }

        $content = (new CreateModel($modelBinding, $domainPath))->generate();
        file_put_contents($fileName, $content);
        $this->output?->info("✓ Model created: {$fileName}");
        return false;
    }

    public function generateService(array $modelBinding, string $domainPath): bool
    {
        $fileName = app_path("Services/{$domainPath}/{$modelBinding['className']}Service.php");
        $this->fileHelper->createDirectoryIfNotExists($fileName);

        if ($this->fileHelper->isFileExists($fileName)) {
            $this->output?->warn("Service already exists: {$fileName}");
            return true;
        }

        $content = (new CreateService($modelBinding, $domainPath))->generate();
        file_put_contents($fileName, $content);
        $this->output?->info("✓ Service created: {$fileName}");
        return false;
    }

    public function generateRepository(array $modelBinding, string $domainPath): bool
    {
        $fileName = app_path("Repositories/{$domainPath}/{$modelBinding['className']}Repository.php");
        $this->fileHelper->createDirectoryIfNotExists($fileName);

        if ($this->fileHelper->isFileExists($fileName)) {
            $this->output?->warn("Repository already exists: {$fileName}");
            return true;
        }

        $content = (new CreateRepository($modelBinding, $domainPath))->generate();
        file_put_contents($fileName, $content);
        $this->output?->info("✓ Repository created: {$fileName}");
        return false;
    }

    public function generateFormRequests(array $modelBinding, string $domainPath): bool
    {
        $directory = app_path("Http/Requests/{$domainPath}/{$modelBinding['className']}");
        $storeFile = "{$directory}/{$modelBinding['className']}StoreRequest.php";
        $updateFile = "{$directory}/{$modelBinding['className']}UpdateRequest.php";

        $this->fileHelper->createDirectoryIfNotExists($storeFile);

        $alreadyExists = false;

        if (! $this->fileHelper->isFileExists($storeFile)) {
            $content = (new CreateFormRequest($modelBinding, $domainPath))->generateStore();
            file_put_contents($storeFile, $content);
            $this->output?->info("✓ Store Request created: {$storeFile}");
        } else {
            $alreadyExists = true;
            $this->output?->warn("Store Request already exists: {$storeFile}");
        }

        if (! $this->fileHelper->isFileExists($updateFile)) {
            $content = (new CreateFormRequest($modelBinding, $domainPath))->generateUpdate();
            file_put_contents($updateFile, $content);
            $this->output?->info("✓ Update Request created: {$updateFile}");
        } else {
            $this->output?->warn("Update Request already exists: {$updateFile}");
        }

        return $alreadyExists;
    }

    public function generateResource(array $modelBinding, string $domainPath): bool
    {
        $directory = app_path("Http/Resources/{$domainPath}/{$modelBinding['className']}");
        $fileName = "{$directory}/{$modelBinding['className']}Resource.php";
        $this->fileHelper->createDirectoryIfNotExists($fileName);

        if ($this->fileHelper->isFileExists($fileName)) {
            $this->output?->warn("Resource already exists: {$fileName}");
            return true;
        }

        $content = (new CreateResource($modelBinding, $domainPath))->generate();
        file_put_contents($fileName, $content);
        $this->output?->info("✓ Resource created: {$fileName}");
        return false;
    }

    public function generateMigration(array $modelBinding): bool
    {
        $tableName = Str::snake(Str::pluralStudly($modelBinding['className']));
        $migrationFileName = database_path('migrations/'.date('Y_m_d_His')."_create_{$tableName}_table.php");
        $substringToMatch = "_create_{$tableName}_table.php";

        $files = scandir(database_path('migrations'));
        foreach ($files as $file) {
            if (str_contains($file, $substringToMatch)) {
                $this->output?->warn("Migration already exists: {$file}");
                return true;
            }
        }

        $content = (new CreateMigration($modelBinding))->generate();
        file_put_contents($migrationFileName, $content);
        $this->output?->info("✓ Migration created: {$migrationFileName}");
        return false;
    }

    public function generateFactory(array $modelBinding, string $domainPath): bool
    {
        $dir = BaseStub::normalizeNamespaceSanitize($domainPath);
        $factoryDir = $dir ? "factories/{$dir}" : 'factories';
        $fileName = database_path("{$factoryDir}/{$modelBinding['className']}Factory.php");
        $this->fileHelper->createDirectoryIfNotExists($fileName);

        if ($this->fileHelper->isFileExists($fileName)) {
            $this->output?->warn("Factory already exists: {$fileName}");
            return true;
        }

        $content = (new CreateFactory($modelBinding, $dir))->generate();
        file_put_contents($fileName, $content);
        $this->output?->info("✓ Factory created: {$fileName}");
        return false;
    }

    public function generateFeatureTest(array $modelBinding, string $domainPath): bool
    {
        $fileName = base_path("tests/Feature/{$domainPath}/{$modelBinding['className']}Test.php");
        $this->fileHelper->createDirectoryIfNotExists($fileName);

        if ($this->fileHelper->isFileExists($fileName)) {
            $this->output?->warn("Test already exists: {$fileName}");
            return true;
        }

        $content = (new CreateFeatureTest($modelBinding, $domainPath))->generate();
        file_put_contents($fileName, $content);
        $this->output?->info("✓ Feature Test created: {$fileName}");
        return false;
    }

    public function generateSeederClass(array $modelBinding, string $domainPath): bool
    {
        // Normalize and sanitize the namespace
        $dir = BaseStub::normalizeNamespaceSanitize($domainPath);

        // Safely build the seeder path
        $seederDir = $dir ? "seeders/{$dir}" : 'seeders';
        $fileName = database_path("{$seederDir}/{$modelBinding['className']}Seeder.php");
        $this->fileHelper->createDirectoryIfNotExists($fileName);

        $exists = false;
        // 1️⃣ Create Seeder File if Not Exists
        if ($this->fileHelper->isFileExists($fileName)) {
            $exists = true;
            $this->output?->warn("Seeder already exists: {$fileName}");
        } else {
            $content = (new CreateSeeder($modelBinding, $domainPath))->generate();
            file_put_contents($fileName, $content);
            $this->output?->info("✓ Seeder created: {$fileName}");
        }

        // 2️⃣ Now Register it Inside DatabaseSeeder.php
        $this->registerSeederInDatabaseSeeder($modelBinding, $dir);

        return $exists;
    }

    private function registerSeederInDatabaseSeeder(array $modelBinding, string $dir): void
    {
        $databaseSeederFile = database_path('seeders/DatabaseSeeder.php');

        if (! file_exists($databaseSeederFile)) {
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
            $this->output?->info("✓ Created new DatabaseSeeder and registered {$modelBinding['className']}Seeder");

            return;
        }

        // Read current content
        $content = file_get_contents($databaseSeederFile);
        $seederClass = "{$modelBinding['className']}Seeder";
        $seederClassWithDomain = ($dir ? $dir.'\\' : '').$seederClass;

        // Add `use` statement if not exists
        if (! str_contains($content, "use Database\\Seeders\\{$seederClassWithDomain};")) {
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

            if (! str_contains($existing, $seederClass)) {
                // Rebuild $this->call([...]) with proper formatting
                $allSeeders = array_filter(array_map(function ($line) {
                    return rtrim(trim($line), ','); // remove spaces + trailing comma
                }, explode("\n", $existing)));
                $allSeeders[] = "{$seederClass}::class";

                // Proper indentation and newlines
                $formattedSeeders = '            '.implode(",\n            ", $allSeeders).',';
                $newCallBlock = "\n".$formattedSeeders."\n        "; // opening and closing brackets indentation

                // Replace old block
                $content = str_replace($matches[1], $newCallBlock, $content);
                file_put_contents($databaseSeederFile, $content);
                $this->output?->info("✓ Registered {$seederClass} in DatabaseSeeder");
            } else {
                $this->output?->warn("Seeder {$seederClass} already registered in DatabaseSeeder");
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
            $this->output?->info("✓ Added run() method and registered {$seederClass}");
        }
    }
}
