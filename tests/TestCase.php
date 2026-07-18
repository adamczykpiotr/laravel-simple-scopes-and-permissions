<?php

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests;

use AdamczykPiotr\SimpleScopesAndPermissions\SimpleScopesAndPermissionsServiceProvider;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums\TestAction;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums\TestPermission;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums\TestScopePermission;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Models\Permission;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Models\Role;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Models\User;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\TestPerformerResolver;
use BackedEnum;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        TestPerformerResolver::$performer = null;

        $this->createSchema();
    }

    protected function getPackageProviders($app)
    {
        return [
            SimpleScopesAndPermissionsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        config()->set('simple-scopes-and-permissions.performer_resolver', TestPerformerResolver::class);
        config()->set('simple-scopes-and-permissions.action_enum', TestAction::class);
        config()->set('simple-scopes-and-permissions.model_permission_enum', TestPermission::class);
        config()->set('simple-scopes-and-permissions.scope_permission_enum', TestScopePermission::class);
    }

    protected function createSchema(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->foreignId('role_id');
            $table->foreignId('permission_id');
        });

        Schema::create('user_has_permissions', function (Blueprint $table) {
            $table->foreignId('user_id');
            $table->foreignId('permission_id');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('role_id')->nullable();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('user_id');
        });
    }

    /**
     * @param array<int, BackedEnum> $rolePermissions
     * @param array<int, BackedEnum> $directPermissions
     */
    protected function createUser(array $rolePermissions = [], array $directPermissions = []): User
    {
        $role = Role::create(['name' => 'role-'.uniqid()]);
        $role->permissions()->attach(collect($rolePermissions)->map($this->permissionId(...)));

        $user = User::create(['name' => 'user-'.uniqid(), 'role_id' => $role->id]);
        $user->permissions()->attach(collect($directPermissions)->map($this->permissionId(...)));

        return $user;
    }

    protected function permissionId(BackedEnum $permission): int
    {
        return Permission::firstOrCreate(['name' => $permission->value])->id;
    }

    protected function actingAsPerformer(User $user): User
    {
        TestPerformerResolver::$performer = $user;

        return $user;
    }
}
