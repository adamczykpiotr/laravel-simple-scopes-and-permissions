<?php

declare(strict_types=1);

namespace AdamczykPiotr\SimpleScopesAndPermissions\Exceptions;

use RuntimeException;

class OperationForbiddenException extends RuntimeException
{

    /**
     * @param string $message
     */
    public function __construct(string $message = 'Operation forbidden due to insufficient permissions')
    {
        parent::__construct($message);
    }
}
