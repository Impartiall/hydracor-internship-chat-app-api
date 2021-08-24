<?php
namespace App\Exceptions;

use App\Exceptions\ClientSafeException;
use GraphQL\Error\ClientAware;

/**
 * A client safe exception for client input errors
 */
class InputException extends ClientSafeException
{
    public function getCategory(): string
    {
        return 'input';
    }
}
