<?php

use AdamczykPiotr\SimpleScopesAndPermissions\Exceptions\OperationForbiddenException;

return [

    /*
    |--------------------------------------------------------------------------
    | Performer resolver
    |--------------------------------------------------------------------------
    |
    | Class resolving the current performer
    | (AdamczykPiotr\SimpleScopesAndPermissions\Contracts\PerformerResolver)
    | used as fallback whenever no explicit $performer is passed to a check.
    |
    */
    'performer_resolver' => null,

    /*
    |--------------------------------------------------------------------------
    | Application enums
    |--------------------------------------------------------------------------
    |
    | Backed string enums implementing the corresponding library contracts:
    | - action_enum: Contracts\Action
    | - model_permission_enum: Contracts\Permissionable
    | - scope_permission_enum: Contracts\ScopePermission
    |
    */
    'action_enum' => null,
    'model_permission_enum' => null,
    'scope_permission_enum' => null,

    /*
    |--------------------------------------------------------------------------
    | Forbidden exception
    |--------------------------------------------------------------------------
    |
    | Throwable instantiated (no constructor arguments) whenever a permission
    | or scope requirement is not met.
    |
    */
    'forbidden_exception' => OperationForbiddenException::class,

];
