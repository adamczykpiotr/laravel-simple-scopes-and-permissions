<?php

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 */
class Permission extends Model
{
    public $timestamps = false;

    protected $guarded = [];
}
