<?php

namespace AdamczykPiotr\SimpleScopesAndPermissions\Concerns;

use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Permissionable;
use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\ScopePermission;
use AdamczykPiotr\SimpleScopesAndPermissions\Support\Permissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Implements the Performer contract for an Eloquent user model.
 * Expects a `role` relation exposing a `permissions` relation, plus a direct
 * `permissions` relation — both returning records with a `name` attribute.
 *
 * @mixin Model
 */
trait HasUserPermissions
{

    /**
     * @var array<string, Model>|null
     */
    protected ?array $combinedRawPermissions = null;

    /**
     * @var Collection<int, ScopePermission>|null
     */
    protected ?Collection $calculatedScopes = null;

    /**
     * @var Collection<int, Permissionable>|null
     */
    protected ?Collection $calculatedPermissions = null;

    /**
     * @return Collection<string, Model>
     */
    public function getRawPermissions(): Collection
    {
        $this->combinedRawPermissions ??= $this->role->permissions
            ->concat($this->permissions)
            ->unique()
            ->keyBy(fn($permission) => $permission->name)
            ->all();

        return collect($this->combinedRawPermissions);
    }

    /**
     * @return Collection<int, ScopePermission>
     */
    public function getScopes(): Collection
    {
        if ($this->calculatedScopes !== null) {
            return $this->calculatedScopes;
        }

        $scopeEnum = Permissions::scopePermissionEnum();

        $this->calculatedScopes = $this->getRawPermissions()
            ->keys()
            ->map(fn(string $permissionName) => $scopeEnum::tryFrom($permissionName))
            ->whereNotNull()
            ->values();

        return $this->calculatedScopes;
    }

    /**
     * @return Collection<int, Permissionable>
     */
    public function getPermissions(): Collection
    {
        if ($this->calculatedPermissions !== null) {
            return $this->calculatedPermissions;
        }

        $permissionEnum = Permissions::modelPermissionEnum();

        $this->calculatedPermissions = $this->getRawPermissions()
            ->keys()
            ->map(fn(string $permissionName) => $permissionEnum::tryFrom($permissionName))
            ->whereNotNull()
            ->values();

        return $this->calculatedPermissions;
    }

    /**
     * @param string $permissionName
     * @return bool
     */
    public function hasRawPermission(string $permissionName): bool
    {
        return $this->getRawPermissions()->has($permissionName);
    }

    /**
     * @param Permissionable $permission
     * @return bool
     */
    public function hasModelPermission(Permissionable $permission): bool
    {
        return $this->hasRawPermission((string) $permission->value);
    }

    /**
     * @param ScopePermission $scope
     * @return bool
     */
    public function hasModelScope(ScopePermission $scope): bool
    {
        return $this->hasRawPermission((string) $scope->value);
    }

    /**
     * @param Permissionable $permission
     * @return bool
     */
    public function hasAppPermission(Permissionable $permission): bool
    {
        return $this->hasRawPermission((string) $permission->value);
    }

    /**
     * @param Permissionable ...$permissions
     * @return void
     * @throws Throwable
     */
    public function requirePermissions(Permissionable ...$permissions): void
    {
        collect($permissions)->each(function (Permissionable $permission) {
            if (!$this->hasRawPermission((string) $permission->value)) {
                throw Permissions::deniedException();
            }
        });
    }

    /**
     * @param Permissionable ...$permissions
     * @return void
     * @throws Throwable
     */
    public function requireOneOfPermissions(Permissionable ...$permissions): void
    {
        $hasAnyPermission = collect($permissions)
            ->reduce(
                fn(bool $carry, Permissionable $permission) => $carry || $this->hasRawPermission((string) $permission->value),
                false
            );

        if (!$hasAnyPermission) {
            throw Permissions::deniedException();
        }
    }
}
