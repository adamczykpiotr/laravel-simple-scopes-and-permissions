<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests;

use AdamczykPiotr\SimpleScopesAndPermissions\SimpleScopesAndPermissionsServiceProvider;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums\TestAction;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums\TestPermission;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums\TestScopePermission;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Models\Permission;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Models\Post;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Models\Role;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Models\User;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\TestPerformerResolver;
use BackedEnum;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        TestPerformerResolver::$performer = null;

        $this->createSchema();
    }

    /**
     * @param mixed $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app)
    {
        return [
            SimpleScopesAndPermissionsServiceProvider::class,
        ];
    }

    /**
     * @param mixed $app
     * @return void
     */
    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        config()->set('simple-scopes-and-permissions.performer_resolver', TestPerformerResolver::class);
        config()->set('simple-scopes-and-permissions.action_enum', TestAction::class);
        config()->set('simple-scopes-and-permissions.model_permission_enum', TestPermission::class);
        config()->set('simple-scopes-and-permissions.scope_permission_enum', TestScopePermission::class);
    }

    /**
     * @return void
     */
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
     * @return User
     */
    protected function createUser(array $rolePermissions = [], array $directPermissions = []): User
    {
        $role = new Role();
        $role->name = 'role-'.uniqid();
        $role->save();

        $role->permissions()->attach(collect($rolePermissions)->map($this->permissionId(...)));

        $user = new User();
        $user->name = 'user-'.uniqid();
        $user->role_id = $role->id;
        $user->save();

        $user->permissions()->attach(collect($directPermissions)->map($this->permissionId(...)));

        return $user;
    }

    /**
     * @param User $author
     * @param string $title
     * @return Post
     */
    protected function createPost(User $author, string $title = 'A post'): Post
    {
        $post = new Post();
        $post->title = $title;
        $post->user_id = $author->id;
        $post->save();

        return $post;
    }

    /**
     * @param BackedEnum $permission
     * @return int
     */
    protected function permissionId(BackedEnum $permission): int
    {
        $existingPermission = Permission::query()
            ->where(Permission::ATTRIBUTE_NAME, $permission->value)
            ->first();

        if ($existingPermission !== null) {
            return $existingPermission->id;
        }

        $newPermission = new Permission();
        $newPermission->name = (string) $permission->value;
        $newPermission->save();

        return $newPermission->id;
    }

    /**
     * @param User $user
     * @return User
     */
    protected function actingAsPerformer(User $user): User
    {
        TestPerformerResolver::$performer = $user;

        return $user;
    }
}
