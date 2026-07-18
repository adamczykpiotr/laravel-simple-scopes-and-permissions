<?php

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums;

use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\ScopePermission;
use AdamczykPiotr\SimpleScopesAndPermissions\Enums\PermissionType;
use AdamczykPiotr\SimpleScopesAndPermissions\Enums\ScopeType;
use BackedEnum;

enum TestScopePermission: string implements ScopePermission
{
    case POST_ALL = 'scope.post.all';
    case POST_OWN = 'scope.post.own';

    public function provideScopeType(): ScopeType
    {
        return match ($this) {
            self::POST_ALL => ScopeType::ALL,
            self::POST_OWN => ScopeType::OWN,
        };
    }

    public function provideRequiredPermissions(): array
    {
        return [];
    }

    public function provideInheritedFromPermissions(): array
    {
        return match ($this) {
            self::POST_OWN => [self::POST_ALL],
            default => [],
        };
    }

    public function provideModelType(): ?BackedEnum
    {
        return null;
    }

    public static function provideType(): PermissionType
    {
        return PermissionType::MODEL_SCOPE_PERMISSION;
    }

    public static function provideHidden(): array
    {
        return [];
    }
}
