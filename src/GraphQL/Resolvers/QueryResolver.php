<?php

namespace App\GraphQL\Resolvers;

class QueryResolver
{
    /**
     * Get a user by their ID
     * 
     * @param array $args The arguments passed to the field
     * @param array $context The global context
     * 
     * @return array|null The specified user
     */
    public static function user($_, array $args, array $context)
    {
        return $context['db']->fetchAssociative(
            'SELECT * FROM users WHERE id = ?',
            [$args['id']]
        );
    }
}