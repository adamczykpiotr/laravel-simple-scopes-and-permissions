<?php

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests\Feature;

use AdamczykPiotr\SimpleScopesAndPermissions\Exceptions\OperationForbiddenException;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums\TestPermission;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums\TestScopePermission;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\TestCase;

class PerformerPermissionsTest extends TestCase
{
    public function testCombinesRolePermissionsWithDirectPermissions(): void
    {
        $user = $this->createUser(
            rolePermissions: [TestPermission::POST_VIEW],
            directPermissions: [TestPermission::POST_UPDATE],
        );

        $this->assertTrue($user->hasModelPermission(TestPermission::POST_VIEW));
        $this->assertTrue($user->hasModelPermission(TestPermission::POST_UPDATE));
        $this->assertFalse($user->hasModelPermission(TestPermission::POST_DELETE));
    }

    public function testHasRawPermissionChecksByName(): void
    {
        $user = $this->createUser(rolePermissions: [TestPermission::POST_VIEW]);

        $this->assertTrue($user->hasRawPermission('permission.post.view'));
        $this->assertFalse($user->hasRawPermission('permission.post.delete'));
        $this->assertFalse($user->hasRawPermission('unknown.permission'));
    }

    public function testHasModelScope(): void
    {
        $user = $this->createUser(rolePermissions: [TestScopePermission::POST_OWN]);

        $this->assertTrue($user->hasModelScope(TestScopePermission::POST_OWN));
        $this->assertFalse($user->hasModelScope(TestScopePermission::POST_ALL));
    }

    public function testGetScopesMapsOnlyScopePermissions(): void
    {
        $user = $this->createUser(rolePermissions: [
            TestPermission::POST_VIEW,
            TestScopePermission::POST_OWN,
        ]);

        $this->assertSame([TestScopePermission::POST_OWN], $user->getScopes()->values()->all());
    }

    public function testGetPermissionsMapsOnlyModelPermissions(): void
    {
        $user = $this->createUser(rolePermissions: [
            TestPermission::POST_VIEW,
            TestScopePermission::POST_OWN,
        ]);

        $this->assertSame([TestPermission::POST_VIEW], $user->getPermissions()->values()->all());
    }

    public function testRequirePermissionsPassesWhenAllPresent(): void
    {
        $this->expectNotToPerformAssertions();

        $user = $this->createUser(rolePermissions: [TestPermission::POST_VIEW, TestPermission::POST_UPDATE]);

        $user->requirePermissions(TestPermission::POST_VIEW, TestPermission::POST_UPDATE);
    }

    public function testRequirePermissionsThrowsWhenAnyMissing(): void
    {
        $user = $this->createUser(rolePermissions: [TestPermission::POST_VIEW]);

        $this->expectException(OperationForbiddenException::class);

        $user->requirePermissions(TestPermission::POST_VIEW, TestPermission::POST_DELETE);
    }

    public function testRequireOneOfPermissionsPassesWhenAnyPresent(): void
    {
        $this->expectNotToPerformAssertions();

        $user = $this->createUser(directPermissions: [TestPermission::POST_UPDATE]);

        $user->requireOneOfPermissions(TestPermission::POST_VIEW, TestPermission::POST_UPDATE);
    }

    public function testRequireOneOfPermissionsThrowsWhenNonePresent(): void
    {
        $user = $this->createUser();

        $this->expectException(OperationForbiddenException::class);

        $user->requireOneOfPermissions(TestPermission::POST_VIEW, TestPermission::POST_UPDATE);
    }
}
