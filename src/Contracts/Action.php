<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Contracts;

use BackedEnum;

interface Action extends BackedEnum
{

    /**
     * @return static
     */
    public static function genericView(): static;

    /**
     * @return static
     */
    public static function genericList(): static;

    /**
     * @return static
     */
    public static function genericCreate(): static;

    /**
     * @return static
     */
    public static function genericEdit(): static;

    /**
     * @return static
     */
    public static function genericDelete(): static;

    /**
     * Actions to be performed on a concrete model instance
     *
     * @return static[]
     */
    public static function modelCases(): array;

    /**
     * Actions to be performed on a model class
     *
     * @return static[]
     */
    public static function modelClassCases(): array;
}
