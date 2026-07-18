<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Concerns;

use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Model;
use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Performer;
use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\ScopePermission;
use AdamczykPiotr\SimpleScopesAndPermissions\Support\Permissions;
use Throwable;

/**
 * @mixin Model
 */
trait HasPermittedScopes
{

    /**
     * @param ScopePermission $scope
     * @param Performer $performer
     * @return bool
     */
    public function provideScopeVerifier(ScopePermission $scope, Performer $performer): bool
    {
        return false;
    }

    /**
     * @param ScopePermission $scope
     * @param Performer $performer
     * @return callable|null
     */
    public static function provideScopeQueryModifier(ScopePermission $scope, Performer $performer): ?callable
    {
        return fn(BuilderContract $query) => $query;
    }

    /**
     * @param Performer|null $performer
     * @return callable
     */
    public static function applyQueryScope(?Performer $performer = null): callable
    {
        $performer ??= Permissions::currentPerformer();

        $lowestScope = static::getLowestScope($performer);
        $modifier = ($lowestScope !== null)
            ? static::provideScopeQueryModifier($lowestScope, $performer)
            : null;

        // Create fallback in case there is some misconfiguration
        $modifier ??= fn(BuilderContract $query) => $query->whereRaw('1 = 0');

        return $modifier;
    }

    /**
     * @param Performer|null $performer
     * @return bool
     */
    public function hasValidScope(?Performer $performer = null): bool
    {
        $performer ??= Permissions::currentPerformer();

        $validScope = $performer->getScopes()
            ->first(fn(ScopePermission $scope) => $this->provideScopeVerifier($scope, $performer));

        return $validScope !== null;
    }

    /**
     * @param Performer|null $performer
     * @return void
     * @throws Throwable
     */
    public function requireHasValidScope(?Performer $performer = null): void
    {
        if ($this->hasValidScope($performer) === false) {
            throw Permissions::deniedException();
        }
    }

    /**
     * @param Performer|null $performer
     * @return ScopePermission|null
     */
    public static function getLowestScope(?Performer $performer = null): ?ScopePermission
    {
        $performer ??= Permissions::currentPerformer();

        return $performer->getScopes()
            ->filter(fn(ScopePermission $scope) => static::provideScopeQueryModifier($scope, $performer) !== null)
            ->sortBy(fn(ScopePermission $scope) => $scope->provideScopeType()->getConstraintLevel())
            ->first();
    }
}
