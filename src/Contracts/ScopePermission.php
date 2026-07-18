<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Contracts;

use AdamczykPiotr\SimpleScopesAndPermissions\Enums\ScopeType;

interface ScopePermission extends Permissionable
{

    /**
     * @return ScopeType
     */
    public function provideScopeType(): ScopeType;
}
