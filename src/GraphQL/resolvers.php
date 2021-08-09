<?php

namespace App\GraphQL;

/**
 * GraphQL resolver function map
 */
$resolvers = [
    'Query' => [
        'user' => [],
        'logIn' => [],
    ],
    'User' => [
        'id' => [],
        'username' => [],
        'isOnline' => [],
        'servers' => [],
        'email' => [],
        'chats' => [],
        'messages' => [],
    ],
];

/**
 * Generate default resolver map using a naming scheme
 * 
 * If the input map has unspecified resolvers, their
 * names will be generated according to a naming scheme.
 * 
 * Example:
 * <code>
 * <?php
 * insertDefaultResolvers([
 *     'Type' => [
 *          'field' => [],
 *      ]
 * ]);
 * ?>
 * </code>
 * 
 * @param array $resolvers A map of resolver functions
 * 
 * @return array A new map of resolver functions with generated defaults
 */
function insertDefaultResolvers(array $resolvers): array
{
    foreach ($resolvers as $type => $fields) {
        foreach ($fields as $field => $resolver) {
            if ($resolver === []) {
                $resolvers[$type][$field] = ['App\GraphQL\Resolvers\\' . $type . 'Resolver', $field];
            }
        }
    }

    return $resolvers;
}

return insertDefaultResolvers($resolvers);