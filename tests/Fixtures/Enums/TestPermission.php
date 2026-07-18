<?php

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums;

use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Permissionable;
use AdamczykPiotr\SimpleScopesAndPermissions\Enums\PermissionType;
use BackedEnum;

enum TestPermission: string implements Permissionable
{
    case POST_VIEW = 'permission.post.view';
    case POST_CREATE = 'permission.post.create';
    case POST_UPDATE = 'permission.post.update';
    case POST_DELETE = 'permission.post.delete';
    case POST_PUBLISH = 'permission.post.publish';

    public function provideRequiredPermissions(): array
    {
        return match ($this) {
            self::POST_CREATE, self::POST_UPDATE, self::POST_DELETE, self::POST_PUBLISH => [self::POST_VIEW],
            default => [],
        };
    }

    public function provideInheritedFromPermissions(): array
    {
        return [];
    }

    public function provideModelType(): ?BackedEnum
    {
        return null;
    }

    public static function provideType(): PermissionType
    {
        return PermissionType::MODEL_PERMISSION;
    }

    public static function provideHidden(): array
    {
        return [];
    }
}
