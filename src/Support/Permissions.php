<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Support;

use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Action;
use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Performer;
use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\PerformerResolver;
use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Permissionable;
use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\ScopePermission;
use AdamczykPiotr\SimpleScopesAndPermissions\Exceptions\OperationForbiddenException;
use Throwable;

final class Permissions
{

    /**
     * @return Performer
     */
    public static function currentPerformer(): Performer
    {
        $performerResolver = resolve(PerformerResolver::class);

        return $performerResolver->resolve();
    }

    /**
     * @return class-string<Action>
     */
    public static function actionEnum(): string
    {
        /** @var class-string<Action> $actionEnum */
        $actionEnum = config('simple-scopes-and-permissions.action_enum');

        return $actionEnum;
    }

    /**
     * @return class-string<Permissionable>
     */
    public static function modelPermissionEnum(): string
    {
        /** @var class-string<Permissionable> $permissionEnum */
        $permissionEnum = config('simple-scopes-and-permissions.model_permission_enum');

        return $permissionEnum;
    }

    /**
     * @return class-string<ScopePermission>
     */
    public static function scopePermissionEnum(): string
    {
        /** @var class-string<ScopePermission> $scopeEnum */
        $scopeEnum = config('simple-scopes-and-permissions.scope_permission_enum');

        return $scopeEnum;
    }

    /**
     * @return Throwable
     */
    public static function deniedException(): Throwable
    {
        /** @var class-string<Throwable> $exceptionClass */
        $exceptionClass = config('simple-scopes-and-permissions.forbidden_exception', OperationForbiddenException::class);

        return new $exceptionClass();
    }
}
