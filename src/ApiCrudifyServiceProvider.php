<?php

namespace Mehedi8gb\ApiCrudify;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Mehedi8gb\ApiCrudify\Commands\ApiCrudifyCommand;

class ApiCrudifyServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('api-crudify')
            ->hasCommand(ApiCrudifyCommand::class);
    }
}
