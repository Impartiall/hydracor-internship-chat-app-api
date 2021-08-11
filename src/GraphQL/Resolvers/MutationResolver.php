<?php

namespace App\GraphQL\Resolvers;

use App\Database\Records;
use Doctrine\DBAL\Connection;
use Exception;
use Psr\Log\NullLogger;
use ReallySimpleJWT\Token;

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
     * @return array|null The created user, or null if the mutation fails
     */
    public static function createUser($_, array $args, array $context)
    {
        $userInsert = Self::createUserInsert($args['input']);
        if (is_null($userInsert)) {
            return null;
        }
        return Records::insert($context['db'], 'users', $userInsert);
    }

    /**
     * Update a user by their ID
     * 
     * Requires user authorization.
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @return array|null The updated user, or null if the mutation fails
     */
    public static function updateUser($_, array $args, array $context)
    {
        if (!$context['auth']->isAuthForUser($args['id'])) return null;

        $userUpdate = Self::createUserUpdate($args['input']);
        if (is_null($userUpdate)) {
            return null;
        }
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
     * @return array|null The deleted user, or null if the mutation fails
     */
    public static function deleteUser($_, array $args, array $context)
    {
        if (!$context['auth']->isAuthForUser($args['id'])) return null;

        return Records::delete($context['db'], 'users', $args['id']);
    }

    /**
     * Generate a user insert array with validated fields
     * and a hashed password
     * 
     * @param array $userInput The user input array
     * 
     * @return array|null A validated insert array, or null if the method fails
     */
    protected static function createUserInsert(array $userInput)
    {
        $userInsert = Self::createUserUpdate($userInput);

        // Return null unless all required fields are set
        foreach (['username', 'email', 'password'] as $field) {
            if (is_null($userInsert[$field])) {
                return null;
            }
        }

        return $userInsert;
    }

    /**
     * Generate a user update array with validated fields
     * and a hashed password
     * 
     * @param array $userInput The user input array
     * 
     * @return array A validated update array
     */
    protected static function createUserUpdate(array $userInput): array
    {
        if (isset($userInput['username']) && strlen($userInput['username']) >= 1) {
            $username = $userInput['username'];
        }
        if (isset($userInput['email'])) {
            $email = filter_var($userInput['email'], FILTER_VALIDATE_EMAIL) ?: null;
        }
        if (isset($userInput['password']) && strlen($userInput['password']) >= 7) {
            $password = password_hash($userInput['password'], PASSWORD_BCRYPT) ?: null;
        }
        return [
            'username' => $username ?? null,
            'email' => $email ?? null,
            'password' => $password ?? null,
        ];
    }
}
