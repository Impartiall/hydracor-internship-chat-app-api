<?php

namespace App\GraphQL\Resolvers;

use Exception;
use ReallySimpleJWT\Token;

/**
 * A collection of resolver methods for the Query type
 */
class QueryResolver
{
    /**
     * Get a user by their ID
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @return array The specified user
     */
    public static function user($_, array $args, array $context): array
    {
        return $context['db']->fetchAssociative(
            'SELECT * FROM users WHERE id = ?',
            [$args['id']]
        );
    }

    /**
     * Get a chat by its ID
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @return array The specified caht
     */
    public static function chat($_, array $args, array $context): array
    {
        return $context['db']->fetchAssociative(
            'SELECT * FROM chats WHERE id = ?',
            [$args['id']]
        );
    }

    /**
     * Get a server by its ID
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @return array The specified server
     */
    public static function server($_, array $args, array $context): array
    {
        return $context['db']->fetchAssociative(
            'SELECT * FROM servers WHERE id = ?',
            [$args['id']]
        );
    }

    /**
     * Validate a requester's credentials and return a JWT
     * 
     * If the credentials are not valid, null will be returned.
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @return array|null A JWT
     */
    public static function logIn($_, array $args, array $context)
    {
        try {
            $user = $context['db']->fetchAssociative(
                'SELECT * FROM users WHERE email = ?',
                [$args['email']]
            );
            if (password_verify($args['password'], $user['password'])) {
                return Token::create($user['id'], $context['jwt']['secret'], time() + $context['jwt']['lifetime'], '');
            } else {
                return null;
            }
        } catch (Exception $__) {
            return null;
        }
    }
}
