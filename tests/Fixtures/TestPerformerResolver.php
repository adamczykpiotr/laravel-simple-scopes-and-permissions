<?php

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures;

use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Performer;
use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\PerformerResolver;
use RuntimeException;

class TestPerformerResolver implements PerformerResolver
{
    public static ?Performer $performer = null;

    public function resolve(): Performer
    {
        return self::$performer ?? throw new RuntimeException('No current performer set');
    }
}
