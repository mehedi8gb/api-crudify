<?php

namespace Mehedi8gb\ApiCrudify\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BaseClassRestorerService
{
    protected Command $command;
    protected array $restored = [];
    protected array $skipped = [];

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * Ensure all base classes and dependencies exist in the project.
     *
     * @return array True if any file was restored.
     */
    public function ensureBaseClassesExist(?string $domainPath = null): array
    {
        $version = $this->resolveVersionFromDomainPath($domainPath);

        $filesToRestore = [
            // Repositories
            app_path('IContracts/Repositories/IRepository.php') => 'IContracts/Repositories/IRepository.php',
            app_path('IContracts/Repositories/IReadRepository.php') => 'IContracts/Repositories/IReadRepository.php',
            app_path('IContracts/Repositories/IWriteRepository.php') => 'IContracts/Repositories/IWriteRepository.php',
            app_path('Repositories/V1/BaseRepository.php') => 'Repositories/V1/BaseRepository.php',

            // Base
            app_path("Http/Controllers/{$version}/Controller.php") => "Http/Controllers/{$version}/Controller.php",

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
            app_path('Core/Query/HandleApiQueryRequest.php') => 'Core/Query/HandleApiQueryRequest.php',

            // Client Query
            app_path('Core/ClientQuery/Contracts/IClientQueryHandler.php') => 'Core/ClientQuery/Contracts/IClientQueryHandler.php',
            app_path('Core/ClientQuery/Handlers/ClientAbstractQueryHandler.php') => 'Core/ClientQuery/Handlers/ClientAbstractQueryHandler.php',
            app_path('Core/ClientQuery/Handlers/Core/ClientPaginationHandler.php') => 'Core/ClientQuery/Handlers/Core/ClientPaginationHandler.php',
            app_path('Core/ClientQuery/Handlers/Core/ClientRelationHandler.php') => 'Core/ClientQuery/Handlers/Core/ClientRelationHandler.php',
            app_path('Core/ClientQuery/Handlers/Core/ClientSortHandler.php') => 'Core/ClientQuery/Handlers/Core/ClientSortHandler.php',
            app_path('Core/ClientQuery/HandleClientApiQueryRequest.php') => 'Core/ClientQuery/HandleClientApiQueryRequest.php',

            // Default Resource
            app_path('Http/Resources/DefaultResource.php') => 'Http/Resources/DefaultResource.php',

            // Helpers files
            app_path('Helpers/Helpers.php') => 'Helpers/Helpers.php',
            app_path('Helpers/SearchParamMapper.php') => 'Helpers/SearchParamMapper.php',
        ];

        foreach ($filesToRestore as $destinationPath => $stubPath) {
            if (! File::exists($destinationPath)) {
                $success = $this->restoreFile($destinationPath, $stubPath);
                $this->restored[] = [basename($destinationPath), $this->toProjectRelativePath($destinationPath), $success ? '✓ Restored' : '✗ Failed'];
            } else {
                $this->skipped[] = [basename($destinationPath), $this->toProjectRelativePath($destinationPath), '— Existed'];
            }
        }

        return [
            'restored' => $this->restored,
            'skipped' => $this->skipped,
        ];
    }

    protected function restoreFile(string $destinationPath, string $stubPath): bool
    {
        $fullStubPath = __DIR__.'/../Stubs/Core/'.$stubPath;

        if (! File::exists($fullStubPath) && str_contains($stubPath, 'Http/Controllers/')) {
            $fullStubPath = __DIR__.'/../Stubs/Core/Http/Controllers/V1/Controller.php';
        }

        if (! File::exists($fullStubPath)) {
            // Logically should not happen if all stubs are correctly placed in the package
            $this->command->error("Stub not found: {$stubPath}");

            return false;
        }

        $directory = dirname($destinationPath);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::copy($fullStubPath, $destinationPath);

        $normalizedPath = str_replace('\\', '/', $destinationPath);
        if (str_contains($normalizedPath, 'Http/Controllers/')) {
            $this->syncControllerNamespace($destinationPath);
        }

        return true;
    }

    protected function resolveVersionFromDomainPath(?string $domainPath): string
    {
        if (! is_string($domainPath) || trim($domainPath) === '') {
            return 'V1';
        }

        $parts = explode('/', str_replace('\\', '/', $domainPath));
        $candidate = strtoupper(trim($parts[0] ?? ''));

        if (preg_match('/^V\d+$/', $candidate) === 1) {
            return $candidate;
        }

        return 'V1';
    }

    protected function syncControllerNamespace(string $destinationPath): void
    {
        $normalizedPath = str_replace('\\', '/', $destinationPath);

        if (! preg_match('/Http\/Controllers\/([^\/]+)\/Controller\.php$/', $normalizedPath, $matches)) {
            return;
        }

        $version = $matches[1];
        $content = file_get_contents($destinationPath);

        if (! is_string($content)) {
            return;
        }

        $updated = preg_replace(
            '/namespace\s+App\\\\Http\\\\Controllers\\\\[^;]+;/',
            "namespace App\\Http\\Controllers\\{$version};",
            $content,
            1
        );

        if (is_string($updated)) {
            file_put_contents($destinationPath, $updated);
        }
    }

    protected function toProjectRelativePath(string $absolutePath): string
    {
        $normalizedAbsolute = str_replace('\\', '/', $absolutePath);
        $normalizedAppPath = rtrim(str_replace('\\', '/', app_path()), '/');

        if (str_starts_with($normalizedAbsolute, $normalizedAppPath.'/')) {
            return substr($normalizedAbsolute, strlen($normalizedAppPath) + 1);
        }

        return $normalizedAbsolute;
    }
}
