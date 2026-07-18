<?php

namespace AdamczykPiotr\SimpleScopesAndPermissions\Enums;

enum PermissionType: string
{
    case APP_PERMISSION = 'APP_PERMISSION';
    case MODEL_PERMISSION = 'MODEL_PERMISSION';
    case MODEL_SCOPE_PERMISSION = 'MODEL_SCOPE_PERMISSION';
}
