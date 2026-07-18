<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums;

use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\ScopePermission;
use AdamczykPiotr\SimpleScopesAndPermissions\Enums\PermissionType;
use AdamczykPiotr\SimpleScopesAndPermissions\Enums\ScopeType;
use BackedEnum;

enum TestScopePermission: string implements ScopePermission
{
    case POST_ALL = 'scope.post.all';
    case POST_OWN = 'scope.post.own';

    /**
     * @return ScopeType
     */
    public function provideScopeType(): ScopeType
    {
        return match ($this) {
            self::POST_ALL => ScopeType::ALL,
            self::POST_OWN => ScopeType::OWN,
        };
    }

    /**
     * @return static[]
     */
    public function provideRequiredPermissions(): array
    {
        return [];
    }

    /**
     * @return static[]
     */
    public function provideInheritedFromPermissions(): array
    {
        return match ($this) {
            self::POST_OWN => [self::POST_ALL],
            default => [],
        };
    }

    /**
     * @return BackedEnum|null
     */
    public function provideModelType(): ?BackedEnum
    {
        return null;
    }

    /**
     * @return PermissionType
     */
    public static function provideType(): PermissionType
    {
        return PermissionType::MODEL_SCOPE_PERMISSION;
    }

    /**
     * @return static[]
     */
    public static function provideHidden(): array
    {
        return [];
    }
}
