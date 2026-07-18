<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \AdamczykPiotr\SimpleScopesAndPermissions\SimpleScopesAndPermissions
 */
class SimpleScopesAndPermissions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \AdamczykPiotr\SimpleScopesAndPermissions\SimpleScopesAndPermissions::class;
    }
}
