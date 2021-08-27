<?php

namespace App\Validation;

use App\Database\Records;
use App\Exceptions\InputException;
use Doctrine\DBAL\Connection;

define('SERVER_NAME_MIN_LENGTH', 1);
define('SERVER_NAME_MAX_LENGTH', 255);

/**
 * A collection of string validation methods for API input
 * 
 * Each validation method checks its input against some requirements
 * and ensures it is not null, returning its input on success
 * and throwing an InputException on failure.
 */
class Validator {
    /**
     * @var Connection $connection A database connection
     */
    protected Connection $connection;

    /**
     * Create an instance of this class with a database connection
     * 
     * @param Connection $connection A database connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function validateServerName(string $name): string
    {
        Self::assertNotMissing($name, 'name');
        if (strlen($name) < SERVER_NAME_MIN_LENGTH)
            throw new InputException(
                FIELD_INVALID,
                'Field `name` must be at least ' . SERVER_NAME_MIN_LENGTH . 'character(s) long.'
            );
        if (strlen($name) > SERVER_NAME_MAX_LENGTH)
            throw new InputException(
                FIELD_INVALID,
                'Field `name` must be at most ' . SERVER_NAME_MAX_LENGTH . 'character(s) long.'
            );
        
        if (!Records::isUnique($this->connection, 'servers', 'name', $name))
            throw new InputException(NOT_UNIQUE, 'A server with that name already exists.');

        return $name;
    }

    /**
     * Ensures that its input is not null
     * 
     * @param mixed $input
     * @param string $field The name of the field whose
     * value is being checked, for error message purposes.
     * 
     * @throws InputException if its input is null
     */
    public static function assertNotMissing($input, string $field = null)
    {
        if (is_null($input)) {
            if ($field) {
                throw new InputException(FIELD_MISSING, "Field `$field` must be set.");
            } else {
                throw new InputException(FIELD_MISSING, 'Required field must be set.');
            }
        }
    }
}