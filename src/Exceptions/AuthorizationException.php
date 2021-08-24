<?php
namespace App\Exceptions;

use App\Exceptions\ClientSafeException;
use GraphQL\Error\ClientAware;

/**
 * A client safe exception for authorization errors
 */
class AuthorizationException extends ClientSafeException
{
    public function getCategory(): string
    {
        return 'authorization';
    }
}
