<?php

namespace Mehedi8gb\ApiCrudify\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BaseClassRestorerService
{
    protected Command $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * Ensure all base classes and dependencies exist in the project.
     *
     * @return bool True if any file was restored.
     */
    public function ensureBaseClassesExist(): bool
    {
        $anyRestored = false;

        $filesToRestore = [
            // Repositories
            app_path('IContracts/Repositories/IRepository.php') => 'IContracts/Repositories/IRepository.php',
            app_path('IContracts/Repositories/IReadRepository.php') => 'IContracts/Repositories/IReadRepository.php',
            app_path('IContracts/Repositories/IWriteRepository.php') => 'IContracts/Repositories/IWriteRepository.php',
            app_path('Repositories/V1/BaseRepository.php') => 'Repositories/V1/BaseRepository.php',

            // Services
            app_path('IContracts/Services/IService.php') => 'IContracts/Services/IService.php',
            app_path('IContracts/Services/IReadService.php') => 'IContracts/Services/IReadService.php',
            app_path('IContracts/Services/IWriteService.php') => 'IContracts/Services/IWriteService.php',
            app_path('Services/V1/BaseService.php') => 'Services/V1/BaseService.php',

            // Models
            app_path('Models/Model.php') => 'Models/Model.php',

            // Core Query
            app_path('Core/Query/Contracts/IQueryHandler.php') => 'Core/Query/Contracts/IQueryHandler.php',
            app_path('Core/Query/Handlers/AbstractQueryHandler.php') => 'Core/Query/Handlers/AbstractQueryHandler.php',
            app_path('Core/Query/Handlers/Core/FilterHandler.php') => 'Core/Query/Handlers/Core/FilterHandler.php',
            app_path('Core/Query/Handlers/Core/PaginationHandler.php') => 'Core/Query/Handlers/Core/PaginationHandler.php',
            app_path('Core/Query/Handlers/Core/RelationHandler.php') => 'Core/Query/Handlers/Core/RelationHandler.php',
            app_path('Core/Query/Handlers/Core/SoftDeleteHandler.php') => 'Core/Query/Handlers/Core/SoftDeleteHandler.php',
            app_path('Core/Query/Handlers/Core/SortHandler.php') => 'Core/Query/Handlers/Core/SortHandler.php',
            app_path('Core/Query/Handlers/Optimization/CacheHandler.php') => 'Core/Query/Handlers/Optimization/CacheHandler.php',
            app_path('Core/Query/HandleApiQueryRequest.php') => 'Core/Query/HandleApiQueryRequest.php',

            // Client Query
            app_path('Core/ClientQuery/Contracts/IClientQueryHandler.php') => 'Core/ClientQuery/Contracts/IClientQueryHandler.php',
            app_path('Core/ClientQuery/Handlers/ClientAbstractQueryHandler.php') => 'Core/ClientQuery/Handlers/ClientAbstractQueryHandler.php',
            app_path('Core/ClientQuery/Handlers/Core/ClientPaginationHandler.php') => 'Core/ClientQuery/Handlers/Core/ClientPaginationHandler.php',
            app_path('Core/ClientQuery/Handlers/Core/ClientRelationHandler.php') => 'Core/ClientQuery/Handlers/Core/ClientRelationHandler.php',
            app_path('Core/ClientQuery/Handlers/Core/ClientSortHandler.php') => 'Core/ClientQuery/Handlers/Core/ClientSortHandler.php',
            app_path('Core/ClientQuery/HandleClientApiQueryRequest.php') => 'Core/ClientQuery/HandleClientApiQueryRequest.php',
        ];

        foreach ($filesToRestore as $destinationPath => $stubPath) {
            if (!File::exists($destinationPath)) {
                $this->restoreFile($destinationPath, $stubPath);
                $anyRestored = true;
            }
        }

        return $anyRestored;
    }

    protected function restoreFile(string $destinationPath, string $stubPath): void
    {
        $fullStubPath = __DIR__ . '/../Stubs/Core/' . $stubPath;

        if (!File::exists($fullStubPath)) {
            // Logically should not happen if all stubs are correctly placed in the package
            $this->command->error("Stub not found: {$stubPath}");
            return;
        }

        $directory = dirname($destinationPath);
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::copy($fullStubPath, $destinationPath);

        $fileName = basename($destinationPath);
        $this->command->info("✓ {$fileName} restored");
    }
}
