<?php
namespace App\Exceptions;

use GraphQL\Error\Error;

define('ERROR_CODE_MESSAGE_SEPARATOR', '31e60093-263d-406c-a8f5-99007f6eb0e8');

class ClientSafeException extends Error
{
    /**
     * Create a class instance with an error code and a message
     * joined into the message field of the `GraphQL\Error\Error` class
     */
    public function __construct(string $errorCode, string $message)
    {
        parent::__construct();
        $this->message = $errorCode . ERROR_CODE_MESSAGE_SEPARATOR . $message;
    }

    public function isClientSafe(): bool
    {
        return true;
    }
}
