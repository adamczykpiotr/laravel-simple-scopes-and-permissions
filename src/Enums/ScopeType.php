<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Enums;

enum ScopeType: string
{
    case OWN = 'OWN';
    case DEPARTMENT = 'DEPARTMENT';
    case ALL = 'ALL';

    /**
     * The bigger, the more constrained the query becomes
     *
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
