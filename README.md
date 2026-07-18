# simple-scopes-and-permissions

[![Latest Version on Packagist](https://img.shields.io/packagist/v/adamczykpiotr/laravel-simple-scopes-and-permissions.svg?style=flat-square)](https://packagist.org/packages/adamczykpiotr/laravel-simple-scopes-and-permissions)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/adamczykpiotr/laravel-simple-scopes-and-permissions/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/adamczykpiotr/laravel-simple-scopes-and-permissions/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/adamczykpiotr/laravel-simple-scopes-and-permissions.svg?style=flat-square)](https://packagist.org/packages/adamczykpiotr/laravel-simple-scopes-and-permissions)

Simple, explicit permission checks and row-level query scopes constraining what data a user can access.

The package owns the generic machinery; the application declares its own permission enums and wires
them in via configuration:

- **Model permissions** — CRUD-style abilities per model type (may a user view/create/edit/delete at all)
- **Scope permissions** — row-level visibility (`ALL` / `DEPARTMENT` / `OWN`), applied both as an
  instance check and as an Eloquent query constraint, failing closed on misconfiguration
- **Actions** — the concrete operations exposed to consumers (`view`, `list`, `edit`, ...), resolved
  per model
- **Performer** — the user a check is evaluated for; always explicit, with a configurable resolver
  fallback

## Installation

Install the package via Composer:

```bash
composer require adamczykpiotr/laravel-simple-scopes-and-permissions
```

Publish the config file:

```bash
php artisan vendor:publish --tag="simple-scopes-and-permissions-config"
```

## Integration

1. Create your permission enums (backed by `string`) implementing the library contracts:
   - `Contracts\Action` — the operations exposed to consumers (`view`, `list`, `edit`, ...)
   - `Contracts\Permissionable` — CRUD-style model permissions
   - `Contracts\ScopePermission` — row-level visibility scopes, each mapping to a `ScopeType` (`ALL` / `DEPARTMENT` / `OWN`)
2. Create a `Contracts\PerformerResolver` returning the current user (e.g. from the auth guard).
3. Wire everything in `config/simple-scopes-and-permissions.php` (`action_enum`, `model_permission_enum`, `scope_permission_enum`, `performer_resolver`, `forbidden_exception`).
4. On the user model: `use Concerns\HasUserPermissions` and `implements Contracts\Performer`. The model must expose a `role` relation (with a nested `permissions` relation) and a direct `permissions` relation, both returning records with a `name` attribute holding the permission name.
5. On each guarded model: `use Concerns\HasPermittedActions` and override `providePermittedActions()`.
6. On each row-scoped model: `use Concerns\HasPermittedScopes` and override `provideScopeVerifier()` + `provideScopeQueryModifier()`.

Denied checks throw the configured `forbidden_exception`. Query scoping always picks the performer's
least-constrained scope and fails closed (`WHERE 1 = 0`) when no scope applies.

See `tests/Fixtures` for a complete reference integration.

## Usage

```php
// Class-level guards
User::requireIndexable();
User::requireCreatable($performer);

// Instance guards (throw the configured forbidden exception)
$user->requireViewable(performer: $performer);
$user->requireEditable();

// Row-level visibility on queries — always via the performer's least-constrained scope
User::query()->tap(User::applyQueryScope($performer))->get();

// Direct permission checks on the performer
$performer->hasModelPermission(ModelPermission::USER_VIEW);
$performer->requireOneOfPermissions(ModelPermission::USER_VIEW, ModelPermission::USER_UPDATE);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
