<?php

namespace AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Models;

use AdamczykPiotr\SimpleScopesAndPermissions\Concerns\HasPermittedActions;
use AdamczykPiotr\SimpleScopesAndPermissions\Concerns\HasPermittedScopes;
use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Action;
use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Performer;
use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\ScopePermission;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums\TestAction;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums\TestPermission;
use AdamczykPiotr\SimpleScopesAndPermissions\Tests\Fixtures\Enums\TestScopePermission;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $title
 * @property int $user_id
 * @property-read User $user
 */
class Post extends Model
{
    use HasPermittedActions, HasPermittedScopes;

    public $timestamps = false;

    protected $guarded = [];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function providePermittedActions(Action $action, Performer $performer): ?bool
    {
        return match ($action) {
            TestAction::GENERIC_LIST => $performer->hasModelPermission(TestPermission::POST_VIEW),
            TestAction::GENERIC_CREATE => $performer->hasModelPermission(TestPermission::POST_CREATE),

            TestAction::GENERIC_VIEW => $this->hasValidScope($performer) && $performer->hasModelPermission(TestPermission::POST_VIEW),
            TestAction::GENERIC_EDIT => $this->hasValidScope($performer) && $performer->hasModelPermission(TestPermission::POST_UPDATE),
            TestAction::GENERIC_DELETE => $this->hasValidScope($performer) && $performer->hasModelPermission(TestPermission::POST_DELETE),
            TestAction::POST_MODEL_PUBLISH => $this->hasValidScope($performer) && $performer->hasModelPermission(TestPermission::POST_PUBLISH),

            default => null,
        };
    }

    public function provideScopeVerifier(ScopePermission $scope, Performer $performer): bool
    {
        assert($performer instanceof User);

        return match ($scope) {
            TestScopePermission::POST_ALL => true,
            TestScopePermission::POST_OWN => $this->user_id === $performer->id,
            default => false,
        };
    }

    public static function provideScopeQueryModifier(ScopePermission $scope, Performer $performer): ?callable
    {
        assert($performer instanceof User);

        return match ($scope) {
            TestScopePermission::POST_ALL => fn(BuilderContract $query) => $query,
            TestScopePermission::POST_OWN => fn(Builder $query) => $query->whereBelongsTo($performer),
            default => null,
        };
    }
}
