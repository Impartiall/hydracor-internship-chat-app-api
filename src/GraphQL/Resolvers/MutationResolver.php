<?php

namespace App\GraphQL\Resolvers;

use App\Database\Records;
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
     * @return string|null A JWT authorizing the created user, or null if the mutation fails
     */
    public static function createUser($_, array $args, array $context)
    {
        $userInsert = Self::createUserInsert($args['input']);
        $user = Records::insert($context['db'], 'users', $userInsert);

        $secret = $context['jwt']['secret'];
        $expiration = time() + $context['jwt']['lifetime'];
        $issuer = 'localhost';
        return Token::create($user['id'], $secret, $expiration, $issuer);
    }

    /**
     * Update a user by their ID
     * 
     * Requires user authorization.
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @return array The updated user
     */
    public static function updateUser($_, array $args, array $context)
    {
        $context['auth']->assert('isAuthForUser', [$args['id']]);

        $userUpdate = Self::createUserUpdate($args['input']);

        return Records::update($context['db'], 'users', $args['id'], $userUpdate);
    }

    /**
     * Delete a user by their ID
     * 
     * Requires user authorization.
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @return array The deleted user
     */
    public static function deleteUser($_, array $args, array $context)
    {
        $context['auth']->assert('isAuthForUser', [$args['id']]);

        return Records::delete($context['db'], 'users', $args['id']);
    }

    /**
     * Generate a user insert array with validated fields
     * and a hashed password
     * 
     * @param array $userInput The user input array
     * 
     * @throws InputException if a field is missing or invalid
     * 
     * @return array A validated insert array
     */
    protected static function createUserInsert(array $userInput): array
    {
        if (isset($userInput['username'])) {
            if (strlen($userInput['username']) >= USERNAME_MIN_LENGTH) {
                $username = $userInput['username'];
            } else {
                throw new InputException('Field `username` must be at least ' . USERNAME_MIN_LENGTH . ' character(s) long.');
            }
        }
        if (isset($userInput['email'])) {
            $email = filter_var($userInput['email'], FILTER_VALIDATE_EMAIL);
            if (!$email) throw new InputException('Field `email` must be a valid email.');
        }
        if (isset($userInput['password'])) {
            if (strlen($userInput['password']) >= PASSWORD_MIN_LENGTH) {
                $password = password_hash($userInput['password'], PASSWORD_BCRYPT);
                if (!$password) throw new Exception('Failed to hash password');
            } else {
                throw new InputException('Field `password` must be at least ' . PASSWORD_MIN_LENGTH . ' character(s) long.');
            }
        }

        $insertArray = [
            'username' => $username ?? null,
            'email' => $email ?? null,
            'password' => $password ?? null,
        ];
        foreach (['username', 'email', 'password'] as $field) {
            if (is_null($insertArray[$field])) throw new InputException("Field `$field` must be set.");
        }

        return $insertArray;
    }

    /**
     * Generate a user update array with validated fields
     * and a hashed password
     * 
     * @param array $userInput The user input array
     * 
     * @throws InputException if a field is set but invalid
     * 
     * @return array A validated update array
     */
    protected static function createUserUpdate(array $userInput): array
    {
        if (isset($userInput['username'])) {
            if (strlen($userInput['username']) >= USERNAME_MIN_LENGTH) {
                $username = $userInput['username'];
            } else {
                throw new InputException('Field `username` must be at least ' . USERNAME_MIN_LENGTH . ' character(s) long.');
            }
        }
        if (isset($userInput['email'])) {
            $email = filter_var($userInput['email'], FILTER_VALIDATE_EMAIL);
            if (!$email) throw new InputException('Field `email` must be a valid email.');
        }
        if (isset($userInput['password'])) {
            if (strlen($userInput['password']) >= PASSWORD_MIN_LENGTH) {
                $password = password_hash($userInput['password'], PASSWORD_BCRYPT);
                if (!$password) throw new Exception('Failed to hash password');
            } else {
                throw new InputException('Field `password` must be at least ' . PASSWORD_MIN_LENGTH . ' character(s) long.');
            }
        }

        $userUpdate = [
            'username' => $username ?? null,
            'email' => $email ?? null,
            'password' => $password ?? null,
        ];
        if (is_null($userUpdate['username']) && is_null($userUpdate['email']) && is_null($userUpdate['password']))
            throw new InputException('At least one update field must not be null');

        return $userUpdate;
    }
}