<?php

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

    public $timestamps = false;

    protected $guarded = [];

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
        return $this->belongsToMany(Permission::class, 'user_has_permissions');
    }
}
