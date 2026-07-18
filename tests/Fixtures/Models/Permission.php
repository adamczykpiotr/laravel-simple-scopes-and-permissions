<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 */
class Permission extends Model
{
    public const string ATTRIBUTE_ID = 'id';
    public const string ATTRIBUTE_NAME = 'name';

    public $timestamps = false;
}
