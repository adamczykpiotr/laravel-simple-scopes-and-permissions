<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Contracts;

interface PerformerResolver
{

    /**
     * Resolve the performer to be used when no explicit one is passed to a check
     *
     * @return Performer
     */
    public function resolve(): Performer;
}
