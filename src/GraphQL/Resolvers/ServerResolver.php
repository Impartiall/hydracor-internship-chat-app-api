<?php

namespace App\GraphQL\Resolvers;

use App\Database\Records;
use App\Exceptions\DatabaseException;
use Doctrine\DBAL\Connection;
use Exception;

/**
 * A collection of resolver methods for the Server type
 */
class ServerResolver
{
    /**
     * Get a server's id
     * 
     * @param array $server The server being accessed
     * 
     * @return int The server's id
     */
    public static function id(array $server, $_, $__): int
    {
        return $server['id'];
    }

    /**
     * Get a server's name
     * 
     * @param array $server The server being accessed
     * 
     * @return string The server's name
     */
    public static function name(array $server, $_, $__): string
    {
        return $server['name'];
    }

    /**
     * Get a server's owner
     * 
     * @param array $server The server being accessed
     * @param array $context The global context
     * 
     * @return array The user array of the server's owner
     */
    public static function owner(array $server, $_, array $context): array
    {
        return Records::selectById($context['db'], 'users', $server['owner_id']);
    }

    /**
     * Get a list of members of the server
     * 
     * Requires that the requester is authorized
     * to view the server.
     * 
     * @param array $server The server being accessed
     * @param array $context The global context
     * 
     * @return array Members of the server
     */
    public static function users(array $server, $_, array $context): array
    {
        return Records::selectManyFromJoin(
            $context['db'],
            'users',
            'user_id',
            'servers_users',
            'server_id',
            $server['id']
        );
    }

    /**
     * Get a list of channels in the server
     * 
     * Requires that the requester is authorized
     * to view the server.
     * 
     * @param array $server The server being accessed
     * @param array $context The global context
     * 
     * @return array Members of the server
     */
    public static function channels(array $server, $_, array $context): array
    {
        return Records::selectMany($context['db'], 'channels', 'server_id', $server['id']);
    }
}
