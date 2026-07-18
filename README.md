# Simple Scopes & Permissions for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/adamczykpiotr/laravel-simple-scopes-and-permissions.svg?style=flat-square)](https://packagist.org/packages/adamczykpiotr/laravel-simple-scopes-and-permissions)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/adamczykpiotr/laravel-simple-scopes-and-permissions/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/adamczykpiotr/laravel-simple-scopes-and-permissions/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/adamczykpiotr/laravel-simple-scopes-and-permissions.svg?style=flat-square)](https://packagist.org/packages/adamczykpiotr/laravel-simple-scopes-and-permissions)

Answer two questions in one place: **what may this user do**, and **which rows may they see** —
with plain PHP enums, no magic, and a fail-closed guarantee.

```php
// May they? (throws your 403 exception if not)
$document->requireEditable(performer: $user);

// Which rows? (query constrained to what the user may see)
Document::query()->tap(Document::applyQueryScope($user))->get();

// What should the UI show? (per-record action map for your frontend)
$document->getPermittedModelActions($user);
// ['view' => true, 'edit' => true, 'delete' => false, ...]
```

## Why this package

Most permission packages answer "does the user have permission X?" and stop there. In real apps the
harder question is row-level: a manager may edit *users*, but only users **in their own
department**. This package treats that as a first-class concept:

- **Row-level scopes, enforced twice.** Every scope works as an instance check (*may they see this
  record?*) and as an Eloquent query constraint (*which records do we even fetch?*) — so lists and
  detail pages can't disagree.
- **Fails closed.** A missing or misconfigured scope yields `WHERE 1 = 0`. Mistakes hide rows;
  they never leak them.
- **Everything is an enum.** Actions, permissions, and scopes are string-backed enums *you* define
  in your app. No string tables sprinkled through the codebase, full IDE navigation, exhaustive
  `match` statements.
- **No magic.** No global scopes, no observers, no middleware guessing. Every check is an explicit
  call you can read at the call site.
- **Explicit performer.** Every check accepts the user it runs for. The "current user" fallback is
  a single resolver you control — which also makes impersonation trivial.
- **Frontend-friendly.** One call returns a `['action' => bool]` map per record, so the UI can
  enable/disable buttons without re-implementing your rules in JavaScript.

## Installation

```bash
composer require adamczykpiotr/laravel-simple-scopes-and-permissions
php artisan vendor:publish --tag="simple-scopes-and-permissions-config"
```

Supports Laravel 10–13 on PHP 8.3+.

## Quick start

Wire four things in `config/simple-scopes-and-permissions.php`, then guard your models.

**1. Define your enums** — actions, permissions, and scopes are app-owned string enums
implementing the package contracts:

```php
enum Action: string implements \AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Action
{
    case GENERIC_VIEW = 'view';
    case GENERIC_LIST = 'list';
    // ... + genericView()/genericList()/... accessors and modelCases()/modelClassCases()
}

enum Permission: string implements \AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Permissionable
{
    case DOCUMENT_VIEW = 'permission.document.view';
    case DOCUMENT_UPDATE = 'permission.document.update';
    // ...
}

enum Scope: string implements \AdamczykPiotr\SimpleScopesAndPermissions\Contracts\ScopePermission
{
    case DOCUMENT_ALL = 'scope.document.all';
    case DOCUMENT_OWN = 'scope.document.own';
    // ... each mapping to a ScopeType (ALL / DEPARTMENT / OWN by default)
}
```

**2. Tell the package who is acting** — a resolver for when no explicit performer is passed:

```php
class AuthenticatedPerformerResolver implements PerformerResolver
{
    public function resolve(): Performer
    {
        return auth()->user() ?? throw new AuthenticationException();
    }
}
```

**3. Make your user model a performer:**

```php
class User extends Authenticatable implements Performer
{
    use HasUserPermissions; // combines role permissions + direct per-user grants
}
```

**4. Guard your models** with three small hooks:

```php
class Document extends Model
{
    use HasPermittedActions, HasPermittedScopes;

    public function providePermittedActions(Action $action, User $performer): ?bool
    {
        return match ($action) {
            Action::GENERIC_LIST => $performer->hasModelPermission(Permission::DOCUMENT_VIEW),
            Action::GENERIC_VIEW => $this->hasValidScope($performer)
                && $performer->hasModelPermission(Permission::DOCUMENT_VIEW),
            Action::GENERIC_EDIT => $this->hasValidScope($performer)
                && $performer->hasModelPermission(Permission::DOCUMENT_UPDATE),
            default => null,
        };
    }

    public function provideScopeVerifier(Scope $scope, User $performer): bool
    {
        return match ($scope) {
            Scope::DOCUMENT_ALL => true,
            Scope::DOCUMENT_OWN => $this->user_id === $performer->id,
            default => false,
        };
    }

    public static function provideScopeQueryModifier(Scope $scope, User $performer): ?Closure
    {
        return match ($scope) {
            Scope::DOCUMENT_ALL => fn($query) => $query,
            Scope::DOCUMENT_OWN => fn($query) => $query->where('user_id', $performer->id),
            default => null,
        };
    }
}
```

That's it. Controllers become one-liners:

```php
public function index(): JsonResponse
{
    Document::requireIndexable();

    $documents = Document::query()
        ->tap(Document::applyQueryScope())
        ->get();

    return DocumentResource::collection($documents)->response();
}
```

## Going further

The [integration guide](docs/INTEGRATION.md) walks through a complete real-world setup:

- [Authentication & impersonation](docs/INTEGRATION.md#2-the-performer-resolver) — resolving the
  performer from the auth guard, and impersonating other users via a request header
- [Designing the enums](docs/INTEGRATION.md#3-the-permission-enums) — prerequisite permissions,
  scope inheritance, custom scope types beyond ALL/DEPARTMENT/OWN
- [Guarded models](docs/INTEGRATION.md#5-a-guarded-scoped-model) — a department-tree scope, soft
  delete rules, models with actions but no scoping
- [Returning permissions to the frontend](docs/INTEGRATION.md#7-returning-permissions-to-the-frontend)
  — per-record action maps in API resources and a class-level action map for menus and buttons
- [Scoped validation rules](docs/INTEGRATION.md#6-enforcement-in-controllers) — stopping users from
  referencing records they can't see

## Testing

```bash
composer test
composer analyse
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
