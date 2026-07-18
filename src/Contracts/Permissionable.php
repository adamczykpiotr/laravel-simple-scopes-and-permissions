<?php

namespace AdamczykPiotr\SimpleScopesAndPermissions\Contracts;

use BackedEnum;
use AdamczykPiotr\SimpleScopesAndPermissions\Enums\PermissionType;

interface Permissionable extends BackedEnum
{

    /**
     * @return static[]
     */
    public function provideRequiredPermissions(): array;

    /**
     * @return static[]
     */
    public function provideInheritedFromPermissions(): array;

    /**
     * @return BackedEnum|null
     */
    public function provideModelType(): ?BackedEnum;

    /**
     * @return PermissionType
     */
    public static function provideType(): PermissionType;

    /**
     * @return static[]
     */
    public static function provideHidden(): array;
}
