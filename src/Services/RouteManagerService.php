<?php

namespace Mehedi8gb\ApiCrudify\Services;

use Illuminate\Support\Str;

class RouteManagerService
{
    private FileSystemHelper $fileHelper;
    private $output;

    public function __construct(FileSystemHelper $fileHelper, $output = null)
    {
        $this->fileHelper = $fileHelper;
        $this->output = $output;
    }

    public function updateRoutesFile(array $modelBinding, string $domainPath, string $controllerName): void
    {
        $routeFile = base_path('routes/api.php');
        $routeName = Str::kebab($modelBinding['className']);
        $namespace = str_replace('/', '\\', $domainPath);

        $this->addApiResourceRoute($routeFile, $routeName, $controllerName, $domainPath);
        $this->addUseStatement($routeFile, "use App\Http\Controllers\\{$namespace}\\{$controllerName};");
    }

    public function addApiResourceRoute(string $routeFile, string $routeName, string $controllerClass, string $domainPath): void
    {
        $routeContent = file_get_contents($routeFile);
        $prefix = Str::of($domainPath ?? '')->lower()->replace('\\', '/');
        $prefix = $prefix->isNotEmpty() ? "{$prefix}/" : '';

        if (str_contains($routeContent, "Route::apiResource('{$prefix}{$routeName}', {$controllerClass}::class);")) {
            $this->output?->warn("Route already exists for: {$routeName}");
            return;
        }

        $routeContent .= "\nRoute::apiResource('{$prefix}{$routeName}', {$controllerClass}::class);\n";

        file_put_contents($routeFile, $routeContent);
        $this->output?->info("✓ Routes added for: {$routeName}");
    }

    public function addUseStatement(string $routeFile, string $useStatement): void
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
}
