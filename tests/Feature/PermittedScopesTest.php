<?php

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests\Feature;

use AdamczykPiotr\SimpleScopesAndPermissions\Exceptions\OperationForbiddenException;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums\TestScopePermission;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Models\Post;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Models\User;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\TestCase;

class PermittedScopesTest extends TestCase
{
    public function testAllScopeSeesEveryRow(): void
    {
        $user = $this->createUser(rolePermissions: [TestScopePermission::POST_ALL]);
        $this->createPosts($user);

        $this->assertSame(3, Post::query()->tap(Post::applyQueryScope($user))->count());
    }

    public function testOwnScopeSeesOnlyOwnRows(): void
    {
        $user = $this->createUser(rolePermissions: [TestScopePermission::POST_OWN]);
        $this->createPosts($user);

        $posts = Post::query()->tap(Post::applyQueryScope($user))->get();

        $this->assertCount(1, $posts);
        $this->assertSame($user->id, $posts->first()?->user_id);
    }

    public function testNoScopeFailsClosed(): void
    {
        $user = $this->createUser();
        $this->createPosts($user);

        $this->assertSame(0, Post::query()->tap(Post::applyQueryScope($user))->count());
    }

    public function testLeastConstrainedScopeWins(): void
    {
        $user = $this->createUser(rolePermissions: [
            TestScopePermission::POST_OWN,
            TestScopePermission::POST_ALL,
        ]);
        $this->createPosts($user);

        $this->assertSame(TestScopePermission::POST_ALL, Post::getLowestScope($user));
        $this->assertSame(3, Post::query()->tap(Post::applyQueryScope($user))->count());
    }

    public function testGetLowestScopeReturnsNullWithoutScopes(): void
    {
        $this->assertNull(Post::getLowestScope($this->createUser()));
        $this->assertSame(
            TestScopePermission::POST_OWN,
            Post::getLowestScope($this->createUser(rolePermissions: [TestScopePermission::POST_OWN]))
        );
    }

    public function testHasValidScopeVerifiesInstanceVisibility(): void
    {
        $owner = $this->createUser(rolePermissions: [TestScopePermission::POST_OWN]);
        $stranger = $this->createUser(rolePermissions: [TestScopePermission::POST_OWN]);
        $post = Post::create(['title' => 'Owned', 'user_id' => $owner->id]);

        $this->assertTrue($post->hasValidScope($owner));
        $this->assertFalse($post->hasValidScope($stranger));
    }

    public function testRequireHasValidScopeThrowsForInvisibleRow(): void
    {
        $owner = $this->createUser(rolePermissions: [TestScopePermission::POST_OWN]);
        $stranger = $this->createUser(rolePermissions: [TestScopePermission::POST_OWN]);
        $post = Post::create(['title' => 'Owned', 'user_id' => $owner->id]);

        $post->requireHasValidScope($owner);

        $this->expectException(OperationForbiddenException::class);

        $post->requireHasValidScope($stranger);
    }

    private function createPosts(User $user): void
    {
        Post::create(['title' => 'Mine', 'user_id' => $user->id]);
        Post::create(['title' => 'Other 1', 'user_id' => $this->createUser()->id]);
        Post::create(['title' => 'Other 2', 'user_id' => $this->createUser()->id]);
    }
}
