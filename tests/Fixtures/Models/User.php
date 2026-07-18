<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Models;

use AdamczykPiotr\SimpleScopesAndPermissions\Concerns\HasUserPermissions;
use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Performer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property int|null $role_id
 * @property-read Role $role
 * @property-read Collection<int, Permission> $permissions
 */
class User extends Model implements Performer
{
    use HasUserPermissions;

    public const string ATTRIBUTE_ID = 'id';
    public const string ATTRIBUTE_NAME = 'name';
    public const string ATTRIBUTE_ROLE_ID = 'role_id';

    public const string RELATION_ROLE = 'role';
    public const string RELATION_PERMISSIONS = 'permissions';

    public const string TABLE_USER_HAS_PERMISSIONS = 'user_has_permissions';

    public $timestamps = false;

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, self::TABLE_USER_HAS_PERMISSIONS);
    }
}
