<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests\Feature;

use AdamczykPiotr\SimpleScopesAndPermissions\Exceptions\OperationForbiddenException;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums\TestAction;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums\TestPermission;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums\TestScopePermission;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Models\Post;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\TestCase;

class PermittedActionsTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetPermittedModelActionsReturnsResolvedModelCases(): void
    {
        $user = $this->createUser(rolePermissions: [
            TestPermission::POST_VIEW,
            TestScopePermission::POST_ALL,
        ]);
        $post = $this->createPost($user);

        $this->assertSame([
            'view' => true,
            'edit' => false,
            'delete' => false,
            'post.publish' => false,
        ], $post->getPermittedModelActions($user));
    }

    /**
     * @return void
     */
    public function testGetPermittedModelClassActionsReturnsResolvedClassCases(): void
    {
        $user = $this->createUser(rolePermissions: [TestPermission::POST_VIEW]);

        $this->assertSame([
            'list' => true,
            'create' => false,
        ], Post::getPermittedModelClassActions($user));
    }

    /**
     * @return void
     */
    public function testRequireCanReturnsModelWhenActionPermitted(): void
    {
        $user = $this->createUser(rolePermissions: [
            TestPermission::POST_UPDATE,
            TestScopePermission::POST_ALL,
        ]);
        $post = $this->createPost($user);

        $this->assertSame($post, $post->requireCan(TestAction::GENERIC_EDIT, $user));
    }

    /**
     * @return void
     */
    public function testRequireCanThrowsWhenActionForbidden(): void
    {
        $user = $this->createUser(rolePermissions: [TestScopePermission::POST_ALL]);
        $post = $this->createPost($user);

        $this->expectException(OperationForbiddenException::class);

        $post->requireCan(TestAction::GENERIC_EDIT, $user);
    }

    /**
     * @return void
     */
    public function testRequireHelpersUseGenericActions(): void
    {
        $user = $this->createUser(rolePermissions: [
            TestPermission::POST_VIEW,
            TestPermission::POST_CREATE,
            TestScopePermission::POST_ALL,
        ]);
        $post = $this->createPost($user);

        Post::requireIndexable($user);
        Post::requireCreatable($user);
        $post->requireViewable(performer: $user);

        $this->expectException(OperationForbiddenException::class);

        $post->requireDeletable(performer: $user);
    }

    /**
     * @return void
     */
    public function testFallsBackToConfiguredPerformerResolver(): void
    {
        $this->actingAsPerformer($this->createUser(rolePermissions: [TestPermission::POST_VIEW]));

        Post::requireIndexable();

        $this->expectException(OperationForbiddenException::class);

        Post::requireCreatable();
    }
}
