<?php
namespace App\Exceptions;

use GraphQL\Error\Error;

define('ERROR_CODE_CATEGORY_SEPARATOR', '31e60093-263d-406c-a8f5-99007f6eb0e8');

class ClientSafeException extends Error
{
    protected $category;

    protected string $errorCode;

    public function __construct(string $errorCode, string $message)
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
    }

    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * Combine the category and error code into one to
     * retrieve the error code within the limits of the
     * `GraphQL\Error\Error` class
     * 
     * @return string The category and error code joined by a separator
     */
    public function getCategory(): string
    {
        return $this->category . ERROR_CODE_CATEGORY_SEPARATOR . $this->errorCode;
    }
}
