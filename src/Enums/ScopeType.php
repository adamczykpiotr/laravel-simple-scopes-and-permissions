<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Enums;

use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\ScopeType as ScopeTypeContract;

/**
 * Default scope-type set. Applications with different visibility semantics
 * define their own enum implementing the ScopeType contract instead.
 */
enum ScopeType: string implements ScopeTypeContract
{
    case OWN = 'OWN';
    case DEPARTMENT = 'DEPARTMENT';
    case ALL = 'ALL';

    /**
     * @return int
     */
    public function getConstraintLevel(): int
    {
        return match ($this) {
            self::ALL => 0,
            self::DEPARTMENT => 1,
            self::OWN => 2,
        };
    }
}
