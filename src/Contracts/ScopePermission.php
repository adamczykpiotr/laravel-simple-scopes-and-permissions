<?php

namespace AdamczykPiotr\SimpleScopesAndPermissions\Contracts;

use AdamczykPiotr\SimpleScopesAndPermissions\Enums\ScopeType;

interface ScopePermission extends Permissionable
{

    /**
     * @return ScopeType
     */
    public function provideScopeType(): ScopeType;
}
