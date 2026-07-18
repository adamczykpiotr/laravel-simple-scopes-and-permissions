<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests\Feature;

use AdamczykPiotr\SimpleScopesAndPermissions\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    /**
     * @return void
     */
    public function testRegistersPackageConfig(): void
    {
        $this->assertIsArray(config('simple-scopes-and-permissions'));
    }
}
