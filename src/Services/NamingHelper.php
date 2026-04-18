<?php

namespace Mehedi8gb\ApiCrudify\Services;

use Illuminate\Support\Str;

class NamingHelper
{
    /**
     * Format controller name from class name.
     */
    public function formatControllerName(string $name): string
    {
        return str_contains($name, 'Controller') ? $name : $name.'Controller';
    }

    /**
     * Get model binding array for various uses.
     */
    public function getModelBinding(string $controllerName): array
    {
        $className = str_replace('Controller', '', $controllerName);

        return [
            'className' => $className,
            'classVar' => ' '.Str::lower($className),
        ];
    }
}
