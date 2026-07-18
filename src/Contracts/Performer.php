<?php

namespace AdamczykPiotr\SimpleScopesAndPermissions\Contracts;

use Illuminate\Support\Collection;
use Throwable;

interface Performer
{

    /**
     * Raw permission records keyed by permission name
     *
     * @return Collection<string, mixed>
     */
    public function getRawPermissions(): Collection;

    /**
     * @return Collection<int, ScopePermission>
     */
    public function getScopes(): Collection;

    /**
     * @return Collection<int, Permissionable>
     */
    public function getPermissions(): Collection;

    /**
     * @param string $permissionName
     * @return bool
     */
    public function hasRawPermission(string $permissionName): bool;

    /**
     * @param Permissionable $permission
     * @return bool
     */
    public function hasModelPermission(Permissionable $permission): bool;

    /**
     * @param ScopePermission $scope
     * @return bool
     */
    public function hasModelScope(ScopePermission $scope): bool;

    /**
     * @param Permissionable $permission
     * @return bool
     */
    public function hasAppPermission(Permissionable $permission): bool;

    /**
     * @param Permissionable ...$permissions
     * @return void
     * @throws Throwable
     */
    public function requirePermissions(Permissionable ...$permissions): void;

    /**
     * @param Permissionable ...$permissions
     * @return void
     * @throws Throwable
     */
    public function requireOneOfPermissions(Permissionable ...$permissions): void;
}
