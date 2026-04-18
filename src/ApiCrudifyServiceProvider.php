<?php

namespace Mehedi8gb\ApiCrudify;

use Mehedi8gb\ApiCrudify\Commands\ApiCrudifyCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
