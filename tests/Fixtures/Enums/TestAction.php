<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums;

use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Action;

enum TestAction: string implements Action
{
    case GENERIC_VIEW = 'view';
    case GENERIC_LIST = 'list';
    case GENERIC_CREATE = 'create';
    case GENERIC_EDIT = 'edit';
    case GENERIC_DELETE = 'delete';

    // Model-specific
    case POST_MODEL_PUBLISH = 'post.publish';

    /**
     * @return static
     */
    public static function genericView(): static
    {
        return self::GENERIC_VIEW;
    }

    /**
     * @return static
     */
    public static function genericList(): static
    {
        return self::GENERIC_LIST;
    }

    /**
     * @return static
     */
    public static function genericCreate(): static
    {
        return self::GENERIC_CREATE;
    }

    /**
     * @return static
     */
    public static function genericEdit(): static
    {
        return self::GENERIC_EDIT;
    }

    /**
     * @return static
     */
    public static function genericDelete(): static
    {
        return self::GENERIC_DELETE;
    }

    /**
     * @return static[]
     */
    public static function modelCases(): array
    {
        return [
            self::GENERIC_VIEW,
            self::GENERIC_EDIT,
            self::GENERIC_DELETE,
            self::POST_MODEL_PUBLISH,
        ];
    }

    /**
     * @return static[]
     */
    public static function modelClassCases(): array
    {
        return [
            self::GENERIC_LIST,
            self::GENERIC_CREATE,
        ];
    }
}
