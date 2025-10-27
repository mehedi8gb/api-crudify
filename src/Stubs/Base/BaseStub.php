<?php

namespace Mehedi8gb\ApiCrudify\Stubs\Base;

use BadMethodCallException;
use Illuminate\Support\Str;

class BaseStub
{
//    public string $testOutput;
//    public function __construct()
//    {
//        $paths = [
//            'V1/Sales',
//            'v2/Inventory',
//            'Domains/V3/Product',
//            'v12/Orders',
//            'Admin/V1/Users',
//            ' V1 / Sales ',
//            '\\V4\\Billing',
//        ];
//
//        $result = '';
//
//        foreach ($paths as $path) {
//            $normalized = self::normalizeNamespace($path);
//            $result .= $path . " => " . $normalized . PHP_EOL;
//        }
//
//        // Store results for later echo or debugging
//        $this->testOutput = $result;
//    }

    protected function pluralize(string $word): string
    {
        if (str_ends_with($word, 'y')) {
            return substr($word, 0, -1).'ies';
        } elseif (in_array(substr($word, -1), ['s', 'x', 'z']) || in_array(substr($word, -2), ['ch', 'sh'])) {
            return $word.'es';
        } else {
            return $word.'s';
        }
    }

    protected function toCamelCase(string $string): string
    {
        return Str::camel($string);
    }

    /**
     * Normalize namespace path for domain-driven structure.
     *
     * - Removes versioning folders (e.g. V1, V2, Api/V3)
     * - Restricts to a single domain-level subdirectory (e.g., Inventory\Category)
     * - Ensures proper backslash namespace formatting
     */
    public static function normalizeNamespace(string $controllerPath): string
    {
        if (!$controllerPath) return '';
        // Step 1: Normalize all slashes and remove extra spaces
        $controllerPath = trim($controllerPath);
        $controllerPath = str_replace('\\', '/', $controllerPath);
        $controllerPath = preg_replace('/\s+/', '', $controllerPath);

        // Step 2: Split into segments
        $segments = array_filter(explode('/', $controllerPath));

        // Step 3: Remove version directories like V1, v2, v12, etc.
        $segments = array_filter($segments, function ($segment) {
            return ! preg_match('/^v\d+$/i', trim($segment));
        });

        // Step 4: Keep at most 2 levels (Domain/Subdomain)
        $segments = array_slice(array_values($segments), 0, 2);

        // Step 5: Rebuild as namespace
        return implode('\\', $segments);
    }
    /**
     * Normalize a namespace path to get the main directory.
     *
     * @param string $controllerPath
     * @return string
     */
    public function normalizeNamespaceToGetSingleDirectory(string $controllerPath): string
    {
        return self::normalizeNamespaceSanitize($controllerPath);
    }

    /**
     * Core normalization logic (single source of truth).
     */
    public static function normalizeNamespaceSanitize(string $controllerPath): string
    {
        // Step 1: Normalize slashes and remove extra spaces
        $controllerPath = trim($controllerPath);
        $controllerPath = str_replace('\\', '/', $controllerPath);
        $controllerPath = preg_replace('/\s+/', '', $controllerPath);

        // Step 2: Split path into segments
        $segments = array_filter(explode('/', $controllerPath));

        // Step 3: Remove any segment that matches version pattern (V1, v2, V12, etc.)
        $segments = array_filter($segments, fn ($seg) => ! preg_match('/^v\d+$/i', $seg));

        // Step 4: Take only the last remaining segment (main directory)
        $segments = array_values($segments);
        return $segments ? end($segments) : 'Default';
    }

}
