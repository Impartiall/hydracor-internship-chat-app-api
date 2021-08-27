<?php

namespace App\GraphQL\Resolvers;

use App\Auth\Authorization;
use App\Database\Records;
use App\Exceptions\AuthorizationException;
use App\Exceptions\ClientSafeException;
use App\Exceptions\InputException;
use Doctrine\DBAL\Connection;
use Exception;
use Psr\Log\NullLogger;
use ReallySimpleJWT\Token;

define('USERNAME_MIN_LENGTH', 1);
define('PASSWORD_MIN_LENGTH', 7);

/**
 * A collection of resolver methods for the Mutation type
 */
class MutationResolver
{
    /**
     * Create a user
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @return string A JWT authorizing the created user
     */
    public static function createUser($_, array $args, array $context): string
    {
        $userInsert = Self::createUserInsert($context['db'], $args['input']);

        $user = Records::insert($context['db'], 'users', $userInsert);

        $secret = $context['jwt']['secret'];
        $expiration = time() + $context['jwt']['lifetime'];
        $issuer = 'localhost';
        return Token::create($user['id'], $secret, $expiration, $issuer);
    }

    /**
     * Update a user by their ID
     * 
     * Requires that the requester is authorized
     * to edit the user.
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @return array The updated user
     */
    public static function updateUser($_, array $args, array $context): array
    {
        $context['auth']->assert('canEditUser', [$args['id']]);

        $userUpdate = Self::createUserUpdate($context['db'], $args['input']);

        return Records::update($context['db'], 'users', $args['id'], $userUpdate);
    }

    /**
     * Delete a user by their ID
     * 
     * Requires that the requester is authorized
     * to edit the user.
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @return array The deleted user
     */
    public static function deleteUser($_, array $args, array $context): array
    {
        $context['auth']->assert('canEditUser', [$args['id']]);

        return Records::delete($context['db'], 'users', $args['id']);
    }

    /**
     * Generate a user insert array with validated fields
     * and a hashed password
     * 
     * @param Connection $connection A database connection
     * @param array $userInput The user input array
     * 
     * @throws InputException if a field is missing or invalid
     * 
     * @return array A validated insert array
     */
    protected static function createUserInsert(Connection $connection, array $userInput): array
    {
        if (isset($userInput['username'])) {
            if (strlen($userInput['username']) >= USERNAME_MIN_LENGTH) {
                $username = $userInput['username'];
            } else {
                throw new InputException(FIELD_INVALID, 'Field `username` must be at least ' . USERNAME_MIN_LENGTH . ' character(s) long.');
            }
        }
        if (isset($userInput['email'])) {
            $email = filter_var($userInput['email'], FILTER_VALIDATE_EMAIL);
            if (!$email) throw new InputException(FIELD_INVALID, 'Field `email` must be a valid email.');

            if (!Records::isUnique($connection, 'users', 'email', $email))
                throw new InputException(NOT_UNIQUE, 'A user with that email already exists.');
        }
        if (isset($userInput['password'])) {
            if (strlen($userInput['password']) >= PASSWORD_MIN_LENGTH) {
                $password = password_hash($userInput['password'], PASSWORD_BCRYPT);
                if (!$password) throw new Exception('Failed to hash password');
            } else {
                throw new InputException(FIELD_INVALID, 'Field `password` must be at least ' . PASSWORD_MIN_LENGTH . ' character(s) long.');
            }
        }

        $insertArray = [
            'username' => $username ?? null,
            'email' => $email ?? null,
            'password' => $password ?? null,
        ];
        foreach (['username', 'email', 'password'] as $field) {
            if (is_null($insertArray[$field])) throw new InputException(FIELD_MISSING, "Field `$field` must be set.");
        }

        return $insertArray;
    }

    /**
     * Generate a user update array with validated fields
     * and a hashed password
     * 
     * @param Connection $connection A database connection
     * @param array $userInput The user input array
     * 
     * @throws InputException if a field is set but invalid
     * 
     * @return array A validated update array
     */
    protected static function createUserUpdate(Connection $connection, array $userInput): array
    {
        if (isset($userInput['username'])) {
            if (strlen($userInput['username']) >= USERNAME_MIN_LENGTH) {
                $username = $userInput['username'];
            } else {
                throw new InputException(FIELD_INVALID, 'Field `username` must be at least ' . USERNAME_MIN_LENGTH . ' character(s) long.');
            }
        }
        if (isset($userInput['email'])) {
            $email = filter_var($userInput['email'], FILTER_VALIDATE_EMAIL);
            if (!$email) throw new InputException(FIELD_INVALID, 'Field `email` must be a valid email.');

            if (!Records::isUnique($connection, 'users', 'email', $email))
                throw new InputException(NOT_UNIQUE, 'A user with that email already exists.');
        }
        if (isset($userInput['password'])) {
            if (strlen($userInput['password']) >= PASSWORD_MIN_LENGTH) {
                $password = password_hash($userInput['password'], PASSWORD_BCRYPT);
                if (!$password) throw new Exception('Failed to hash password');
            } else {
                throw new InputException(FIELD_INVALID, 'Field `password` must be at least ' . PASSWORD_MIN_LENGTH . ' character(s) long.');
            }
        }

        $userUpdate = [
            'username' => $username ?? null,
            'email' => $email ?? null,
            'password' => $password ?? null,
        ];
        if (is_null($userUpdate['username']) && is_null($userUpdate['email']) && is_null($userUpdate['password']))
            throw new InputException(FIELD_MISSING, 'At least one update field must not be null');

        return $userUpdate;
    }

    /**
     * Create a server with a given name
     * 
     * Requires that the requester is authenticated
     * as an existing user. This user will be the owner
     * of the new server.
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @return array The updated server
     */
    public function createServer($_, array $args, array $context): array
    {
        $context['auth']->assert('isAuthorized', []);
        $owner = $context['auth']->getRequester();

        $server = [
            'owner_id' => $owner['id'],
            'name' => $context['validator']->validateServerName($args['name']),
        ];

        return Records::insert($context['db'], 'servers', $server);
    }

    /**
     * Update a server's name by its ID
     * 
     * Requires that the requester is authorized
     * to edit the server.
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @return array The updated server
     */
    public function updateServerName($_, array $args, array $context): array
    {
        $context['auth']->assert('canEditServer', [$args['id']]);

        $replacements = [
            'name' => $context['validator']->validateServerName($args['name']),
        ];

        return Records::update($context['db'], 'servers', $args['id'], $replacements);
    }

    /**
     * Update a server's owner by its ID
     * 
     * Requires that the requester is authorized
     * to edit the server.
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @return array The updated server
     */
    public function updateServerOwner($_, array $args, array $context): array
    {
        $context['auth']->assert('canEditServer', [$args['id']]);

        // Ensure that the new owner user exists
        $newOwner = Records::selectById($context['db'], 'users', $args['newOwnerId']);

        $replacements = [
            'owner' => $newOwner['id'],
        ];

        return Records::update($context['db'], 'servers', $args['id'], $replacements);
    }

    /**
     * Delete a server by its ID
     * 
     * Requires that the requester is authorized
     * to edit the server.
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @return array The deleted server
     */
    public function deleteServer($_, array $args, array $context): array
    {
        $context['auth']->assert('canEditServer', [$args['id']]);

        return Records::delete($context['db'], 'servers', $args['id']);
    }
}
