<?php

namespace AdamczykPiotr\SimpleScopesAndPermissions\Concerns;

use Illuminate\Database\Eloquent\Model;
use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Action;
use AdamczykPiotr\SimpleScopesAndPermissions\Contracts\Performer;
use AdamczykPiotr\SimpleScopesAndPermissions\Support\Permissions;
use Throwable;

/**
 * @mixin Model
 */
trait HasPermittedActions
{

    /**
     * @param Action $action
     * @param Performer $performer
     * @return bool|null
     */
    public function providePermittedActions(Action $action, Performer $performer): ?bool
    {
        return null;
    }

    /**
     * @param Performer|null $performer
     * @return array<string, bool>
     */
    public function getPermittedModelActions(?Performer $performer = null): array
    {
        $actionEnum = Permissions::actionEnum();

        return $this->getPermittedActions($actionEnum::modelCases(), $performer);
    }

    /**
     * @param Performer|null $performer
     * @return array<string, bool>
     */
    public static function getPermittedModelClassActions(?Performer $performer = null): array
    {
        $actionEnum = Permissions::actionEnum();

        return (new self)->getPermittedActions($actionEnum::modelClassCases(), $performer);
    }

    /**
     * @param array<int, Action> $actions
     * @param Performer|null $performer
     * @return array<string, bool>
     */
    public function getPermittedActions(array $actions, ?Performer $performer = null): array
    {
        $performer ??= Permissions::currentPerformer();

        return collect($actions)
            ->keyBy(fn(Action $action) => (string) $action->value)
            ->map(fn(Action $action) => $this->providePermittedActions($action, $performer))
            ->filter(fn(?bool $permitted) => $permitted !== null)
            ->all();
    }

    /**
     * @param Action $action
     * @param Performer|null $performer
     * @return static
     * @throws Throwable
     */
    public function requireCan(Action $action, ?Performer $performer = null): static
    {
        $performer ??= Permissions::currentPerformer();

        if ($this->providePermittedActions($action, $performer) === false) {
            throw Permissions::deniedException();
        }

        return $this;
    }

    /**
     * @param Action $action
     * @param Performer|null $performer
     * @return self
     * @throws Throwable
     */
    public static function requireModelCan(Action $action, ?Performer $performer = null): self
    {
        return (new self)->requireCan($action, $performer);
    }

    // Helpers

    /**
     * @param Performer|null $performer
     * @return self
     * @throws Throwable
     */
    public static function requireIndexable(?Performer $performer = null): self
    {
        return static::requireModelCan(Permissions::actionEnum()::genericList(), $performer);
    }

    /**
     * @param Performer|null $performer
     * @return self
     * @throws Throwable
     */
    public static function requireCreatable(?Performer $performer = null): self
    {
        return static::requireModelCan(Permissions::actionEnum()::genericCreate(), $performer);
    }

    /**
     * @param Action|null $action
     * @param Performer|null $performer
     * @return static
     * @throws Throwable
     */
    public function requireViewable(?Action $action = null, ?Performer $performer = null): static
    {
        return $this->requireCan($action ?? Permissions::actionEnum()::genericView(), $performer);
    }

    /**
     * @param Action|null $action
     * @param Performer|null $performer
     * @return static
     * @throws Throwable
     */
    public function requireEditable(?Action $action = null, ?Performer $performer = null): static
    {
        return $this->requireCan($action ?? Permissions::actionEnum()::genericEdit(), $performer);
    }

    /**
     * @param Action|null $action
     * @param Performer|null $performer
     * @return static
     * @throws Throwable
     */
    public function requireDeletable(?Action $action = null, ?Performer $performer = null): static
    {
        return $this->requireCan($action ?? Permissions::actionEnum()::genericDelete(), $performer);
    }
}
