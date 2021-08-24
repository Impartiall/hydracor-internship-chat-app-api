<?php

namespace App\GraphQL\Resolvers;

use App\Database\Records;
use App\Exceptions\AuthenticationException;
use Exception;
use ReallySimpleJWT\Token;

/**
 * A collection of resolver methods for the Query type
 */
class QueryResolver
{
    /**
     * Get the current authenticated user
     * 
     * @param array $context The global context
     * 
     * @return array|null The current authenticated user
     */
    public static function me($_, $__, array $context)
    {
        return $context['auth']->getRequester();
    }

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
        return Records::selectById($context['db'], 'users', $args['id']);
    }

    /**
     * Get a chat by its ID
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @return array The specified chat
     */
    public static function chat($_, array $args, array $context): array
    {
        
        return Records::selectById($context['db'], 'chats', $args['id']);
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
        return Records::selectById($context['db'], 'servers', $args['id']);
    }

    /**
     * Validate a requester's credentials and return a JWT
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @throws AuthenticationException if credentials are invalid
     * 
     * @return string A JWT
     */
    public static function logIn($_, array $args, array $context): string
    {
        $user = $context['db']->fetchAssociative(
            'SELECT * FROM users WHERE email = ?',
            [$args['email']]
        );
        if ($user && password_verify($args['password'], $user['password'])) {
            $secret = $context['jwt']['secret'];
            $expiration = time() + $context['jwt']['lifetime'];
            $issuer = 'localhost';
            return Token::create($user['id'], $secret, $expiration, $issuer);
        } else {
            throw new AuthenticationException(CREDENTIALS_INVALID, 'Email or password is incorrect.');
        }
    }
}
