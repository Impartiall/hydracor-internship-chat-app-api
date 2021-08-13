<?php
namespace App\Exceptions;

use App\Exceptions\ClientSafeException;
use GraphQL\Error\ClientAware;

/**
 * A client safe exception for JWT authentication errors
 */
class AuthenticationException extends ClientSafeException implements ClientAware
{
    public function getCategory(): string
    {
        return 'authentication';
    }
}
