<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions;

use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\PerformerResolver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SimpleScopesAndPermissionsServiceProvider extends PackageServiceProvider
{

    /**
     * @param Package $package
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('simple-scopes-and-permissions')
            ->hasConfigFile('simple-scopes-and-permissions');
    }

    /**
     * @return void
     */
    public function packageRegistered(): void
    {
        $this->app->bind(PerformerResolver::class, function ($app) {
            /** @var class-string<PerformerResolver> $resolverClass */
            $resolverClass = config('simple-scopes-and-permissions.performer_resolver');

            return $app->make($resolverClass);
        });
    }
}
