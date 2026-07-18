<?php

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests\Feature;

use AdamczykPiotr\SimpleScopesAndPermissions\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function testRegistersPackageConfig(): void
    {
        $this->assertIsArray(config('simple-scopes-and-permissions'));
    }
}
