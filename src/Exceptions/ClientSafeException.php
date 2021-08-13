<?php
namespace App\Exceptions;

class ClientSafeException extends \Exception
{
    public function isClientSafe(): bool
    {
        return true;
    }
}
