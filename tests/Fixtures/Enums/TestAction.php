<?php

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

    public static function genericView(): static
    {
        return self::GENERIC_VIEW;
    }

    public static function genericList(): static
    {
        return self::GENERIC_LIST;
    }

    public static function genericCreate(): static
    {
        return self::GENERIC_CREATE;
    }

    public static function genericEdit(): static
    {
        return self::GENERIC_EDIT;
    }

    public static function genericDelete(): static
    {
        return self::GENERIC_DELETE;
    }

    public static function modelCases(): array
    {
        return [
            self::GENERIC_VIEW,
            self::GENERIC_EDIT,
            self::GENERIC_DELETE,
            self::POST_MODEL_PUBLISH,
        ];
    }

    public static function modelClassCases(): array
    {
        return [
            self::GENERIC_LIST,
            self::GENERIC_CREATE,
        ];
    }
}
