<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property-read Collection<int, Permission> $permissions
 */
class Role extends Model
{
    public const string ATTRIBUTE_ID = 'id';
    public const string ATTRIBUTE_NAME = 'name';

    public const string RELATION_PERMISSIONS = 'permissions';

    public const string TABLE_ROLE_HAS_PERMISSIONS = 'role_has_permissions';

    public $timestamps = false;

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, self::TABLE_ROLE_HAS_PERMISSIONS);
    }
}
