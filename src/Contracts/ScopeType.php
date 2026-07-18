<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Contracts;

interface ScopeType
{

    /**
     * The bigger, the more constrained the query becomes
     *
     * @return int
     */
    public function getConstraintLevel(): int;
}
