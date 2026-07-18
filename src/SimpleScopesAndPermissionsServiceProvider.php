<?php

namespace AdamczykPiotr\SimpleScopesAndPermissions;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SimpleScopesAndPermissionsServiceProvider extends PackageServiceProvider {

    /**
     * @param Package $package
     * @return void
     */
    public function configurePackage(Package $package): void {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('simple-scopes-and-permissions')
            ->hasConfigFile('simple-scopes-and-permissions');
    }
}
