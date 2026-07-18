<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Contracts;

interface ScopePermission extends Permissionable
{

    /**
     * @return ScopeType
     */
    public function provideScopeType(): ScopeType;
}
