# Integration guide

A complete, real-world integration modeled on the Iodamaster application: users belong to
departments (a tree), permissions come from a role plus direct per-user grants, and every model is
guarded by CRUD permissions and row-level visibility scopes.

## 1. Configuration

`config/simple-scopes-and-permissions.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\Role\ModelAction;
use App\Enums\Role\Permissions\Permissionable\ModelPermission;
use App\Enums\Role\Permissions\Permissionable\ModelScopePermission;
use App\Exceptions\Permissions\OperationForbiddenException;
use App\Helpers\CurrentPerformerResolver;

return [

    'performer_resolver' => CurrentPerformerResolver::class,

    'action_enum' => ModelAction::class,
    'model_permission_enum' => ModelPermission::class,
    'scope_permission_enum' => ModelScopePermission::class,

    'forbidden_exception' => OperationForbiddenException::class,

];
```

- `forbidden_exception` lets denied checks throw *your* exception, so your API exception handler
  keeps producing its usual error envelope (Iodamaster maps it to a 403 with a stable code name).
- The resolver is the fallback for every check where no explicit `$performer` is passed.

## 2. The performer resolver

The resolver returns "the user this check runs for" when the caller didn't pass one. Iodamaster
resolves it from the authenticated user and additionally supports impersonation via an
`X-Impersonate-User-Id` header (gated on its own permission):

```php
<?php

declare(strict_types=1);

namespace App\Helpers;

use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Performer;
use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\PerformerResolver;

class CurrentPerformerResolver implements PerformerResolver
{

    /**
     * @return Performer
     */
    public function resolve(): Performer
    {
        return Helper::currentUser();
    }
}
```

> Services should still receive the performer explicitly (`requireViewable(performer: $user)`) —
> the resolver exists for HTTP entry points, not as a service-layer crutch. Relying on the ambient
> performer inside queued jobs or console commands is a bug waiting to happen.

## 3. The permission enums

Three string-backed enums implement the library contracts. Permission *names* are what gets stored
in the `permissions` table; the enums give them types and behaviour.

### Actions — `Contracts\Action`

The operations exposed to consumers, split into instance actions and class-level actions:

```php
<?php

declare(strict_types=1);

namespace App\Enums\Role;

use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Action;

enum ModelAction: string implements Action
{
    case GENERIC_VIEW = 'view';
    case GENERIC_LIST = 'list';
    case GENERIC_CREATE = 'create';
    case GENERIC_EDIT = 'edit';
    case GENERIC_DELETE = 'delete';

    // Model-specific
    case USER_MODEL_UPDATE_PASSWORD = 'user.updatePassword';
    case PROCESS_MODEL_HANDLE = 'process.handle';
    case PROCESS_MODEL_CANCEL = 'process.cancel';

    // Class-specific
    case ROLE_CLASS_MANAGE = 'role.manage';
    case USER_CLASS_IMPERSONATE = 'user.impersonate';

    /**
     * @return static
     */
    public static function genericView(): static
    {
        return self::GENERIC_VIEW;
    }

    // ... genericList() / genericCreate() / genericEdit() / genericDelete() likewise

    /**
     * Actions to be performed on a concrete model instance
     *
     * @return static[]
     */
    public static function modelCases(): array
    {
        return [
            self::GENERIC_VIEW,
            self::GENERIC_EDIT,
            self::GENERIC_DELETE,
            self::USER_MODEL_UPDATE_PASSWORD,
            self::PROCESS_MODEL_HANDLE,
            self::PROCESS_MODEL_CANCEL,
        ];
    }

    /**
     * Actions to be performed on a model class
     *
     * @return static[]
     */
    public static function modelClassCases(): array
    {
        return [
            self::GENERIC_LIST,
            self::GENERIC_CREATE,
            self::ROLE_CLASS_MANAGE,
            self::USER_CLASS_IMPERSONATE,
        ];
    }
}
```

### Model permissions — `Contracts\Permissionable`

CRUD-style abilities per model type. `provideRequiredPermissions()` declares prerequisites
(creating a user requires seeing users and departments), which permission-management UIs can use:

```php
enum ModelPermission: string implements Permissionable
{
    case USER_VIEW = 'permission.user.view';
    case USER_CREATE = 'permission.user.create';
    case USER_UPDATE = 'permission.user.update';
    case USER_DELETE = 'permission.user.delete';

    case DEPARTMENT_VIEW = 'permission.department.view';
    // ...

    /**
     * @return static[]
     */
    public function provideRequiredPermissions(): array
    {
        return match ($this) {
            self::USER_CREATE => [self::USER_VIEW, self::DEPARTMENT_VIEW],
            default => [],
        };
    }

    // provideInheritedFromPermissions() / provideModelType() / provideType() / provideHidden()
}
```

### Scope permissions — `Contracts\ScopePermission`

Row-level visibility. Each scope maps to a `ScopeType` (`ALL` = least constrained,
`DEPARTMENT`, `OWN` = most constrained). When a performer holds several scopes for a model, the
**least-constrained one wins** for query scoping:

```php
enum ModelScopePermission: string implements ScopePermission
{
    case USER_ALL = 'scope.user.all';
    case USER_DEPARTMENT = 'scope.user.department';
    case USER_OWN = 'scope.user.own';

    case DEPARTMENT_ALL = 'scope.department.all';
    case DEPARTMENT_OWN = 'scope.department.own';
    // ...

    /**
     * @return ScopeType
     */
    public function provideScopeType(): ScopeType
    {
        return match ($this) {
            self::USER_ALL, self::DEPARTMENT_ALL => ScopeType::ALL,
            self::USER_DEPARTMENT => ScopeType::DEPARTMENT,
            self::USER_OWN, self::DEPARTMENT_OWN => ScopeType::OWN,
        };
    }

    /**
     * Higher scopes imply lower ones — used by permission-management UIs
     *
     * @return static[]
     */
    public function provideInheritedFromPermissions(): array
    {
        return match ($this) {
            self::USER_OWN => [self::USER_ALL, self::USER_DEPARTMENT],
            self::USER_DEPARTMENT => [self::USER_ALL],
            self::DEPARTMENT_OWN => [self::DEPARTMENT_ALL],
            default => [],
        };
    }

    // ...
}
```

## 4. The user model

The user model implements `Performer` via the `HasUserPermissions` concern. The concern combines
the role's permissions with direct per-user grants, keyed by permission name. It expects a `role`
relation (with a nested `permissions` relation) and a direct `permissions` relation:

```php
use AdamczykPiotr\SimpleScopesAndPermissions\Concerns\HasPermittedActions;
use AdamczykPiotr\SimpleScopesAndPermissions\Concerns\HasPermittedScopes;
use AdamczykPiotr\SimpleScopesAndPermissions\Concerns\HasUserPermissions;
use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Performer;

class User extends Authenticatable implements Performer
{
    use HasUserPermissions, HasPermittedActions, HasPermittedScopes;

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
```

(`User` also uses `HasPermittedActions`/`HasPermittedScopes` because users are themselves a guarded,
scoped resource — who may see or edit which users.)

## 5. A guarded, scoped model

`Department` is Iodamaster's canonical example: a tree where `DEPARTMENT_OWN` limits visibility to
the performer's own subtree. Three hooks drive everything:

```php
use AdamczykPiotr\SimpleScopesAndPermissions\Concerns\HasPermittedActions;
use AdamczykPiotr\SimpleScopesAndPermissions\Concerns\HasPermittedScopes;

class Department extends BaseModel
{
    use HasPermittedActions, HasPermittedScopes;

    /**
     * Action authorization — permission checks plus instance state
     *
     * @param ModelAction $action
     * @param User $performer
     * @return bool|null
     */
    public function providePermittedActions(ModelAction $action, User $performer): ?bool
    {
        return match ($action) {
            ModelAction::GENERIC_LIST => $performer->hasModelPermission(ModelPermission::DEPARTMENT_VIEW),
            ModelAction::GENERIC_CREATE => $performer->hasModelPermission(ModelPermission::DEPARTMENT_CREATE),

            ModelAction::GENERIC_VIEW => $this->hasValidScope($performer)
                && $performer->hasModelPermission(ModelPermission::DEPARTMENT_VIEW),
            ModelAction::GENERIC_DELETE => $this->deleted_at === null
                && $this->childrenDepartments->isEmpty()
                && $this->hasValidScope($performer)
                && $performer->hasModelPermission(ModelPermission::DEPARTMENT_DELETE),

            default => null, // action not applicable to this model
        };
    }

    /**
     * Instance-level visibility — is THIS row within the scope
     *
     * @param ModelScopePermission $scope
     * @param User $performer
     * @return bool
     */
    public function provideScopeVerifier(ModelScopePermission $scope, User $performer): bool
    {
        return match ($scope) {
            ModelScopePermission::DEPARTMENT_ALL => true,
            ModelScopePermission::DEPARTMENT_OWN => $performer->department_tree->find($this->id) !== null,
            default => false,
        };
    }

    /**
     * Query-level visibility — constrain a Builder to visible rows
     *
     * @param ModelScopePermission $scope
     * @param User $performer
     * @return null|Closure(BuilderContract): BuilderContract
     */
    public static function provideScopeQueryModifier(ModelScopePermission $scope, User $performer): ?Closure
    {
        return match ($scope) {
            ModelScopePermission::DEPARTMENT_ALL => fn(BuilderContract $query) => $query,
            ModelScopePermission::DEPARTMENT_OWN => fn(BuilderContract $query) => $query->whereIn(
                self::ATTRIBUTE_ID,
                $performer->department_tree->pluck(self::ATTRIBUTE_ID)
            ),
            default => null,
        };
    }
}
```

Conventions the hooks follow:

- `providePermittedActions()` returns `null` for actions that don't apply to the model — they're
  simply omitted from the permitted-actions map and never throw.
- Class-level actions (`GENERIC_LIST`, `GENERIC_CREATE`) check permissions only; instance actions
  additionally require `hasValidScope($performer)` and any instance state (soft-deleted, has
  children, ...).
- Model overrides may narrow the hook signatures to the app's concrete types (`ModelAction`,
  `User`) — the trait methods are replaced wholesale, so PHP applies no variance checks.
- Returning `null` from `provideScopeQueryModifier()` for an unhandled scope is safe: with no
  applicable scope, `applyQueryScope()` falls back to `WHERE 1 = 0` — **misconfiguration hides
  rows, it never leaks them**.

A model with actions but no row scoping (Iodamaster's `Role`) uses only `HasPermittedActions` and
gates on an app-level permission instead of scopes.

## 6. Enforcement in controllers

Controllers are the enforcement layer. The Iodamaster pattern per endpoint:

```php
class UserController extends Controller
{
    public function index(): JsonResponse
    {
        User::requireIndexable();                          // 403 unless LIST permitted

        $users = User::query()
            ->tap(User::applyQueryScope())                 // only rows the performer may see
            ->get();

        return Resp::success(UserSimpleResource::collection($users));
    }

    public function update(User $user, UserUpdateInput $input, UserService $service): JsonResponse
    {
        $user->requireEditable();                          // scope + permission + instance state

        $user = $service->update($user, $input, performer: Helper::currentUser());

        return Resp::success(new UserResource($user));
    }

    public function updatePassword(User $user, PasswordInput $input): JsonResponse
    {
        $user->requireCan(ModelAction::USER_MODEL_UPDATE_PASSWORD);
        // ...
    }
}
```

Two supporting patterns from Iodamaster worth copying:

- **Expose permitted actions to the frontend** — API resources call
  `$model->getPermittedModelActions()` and include the map in the payload, so the UI can
  enable/disable buttons without duplicating authorization rules client-side.
- **Scope validation-rule lookups** — rules that check "referenced record exists" apply
  `Model::applyQueryScope()` to the existence query, so users can't reference rows they cannot see.

## 7. Direct permission checks

Anywhere a performer instance is available:

```php
$performer->hasModelPermission(ModelPermission::USER_VIEW);
$performer->hasModelScope(ModelScopePermission::USER_ALL);
$performer->requirePermissions(ModelPermission::USER_VIEW, ModelPermission::USER_UPDATE); // AND
$performer->requireOneOfPermissions(ModelPermission::USER_VIEW, ModelPermission::USER_UPDATE); // OR
```
