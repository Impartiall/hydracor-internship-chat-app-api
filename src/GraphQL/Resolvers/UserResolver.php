<?php

namespace App\GraphQL\Resolvers;

use Doctrine\DBAL\Connection;

/**
 * A collection of resolver methods for the User type
 */
class UserResolver
{
    /**
     * Get a user's id
     * 
     * @param array $user The user being accessed
     * 
     * @return int The user's id
     */
    public static function id(array $user, $_, $__): int
    {
        return $user['id'];
    }

    /**
     * Get a user's username
     * 
     * @param array $user The user being accessed
     * 
     * @return string The user's username
     */
    public static function username(array $user, $_, $__): string
    {
        return $user['username'];
    }
    
    /**
     * Get a user's online status
     * 
     * @param array $user The user being accessed
     * 
     * @return bool The user's online status
     */
    public static function isOnline(array $user, $_, $__): bool
    {
        return $user['is_online'];
    }

    /**
     * Get a list of servers the user is a member of
     * 
     * @param array $user The user being accessed
     * @param array $context The global context
     * 
     * @return array Servers the user is a member of, including those that they own
     */
    public static function servers(array $user, $_, array $context): array
    {
        $serverIds = $context['db']->fetchFirstColumn(
            'SELECT server_id FROM servers_users WHERE user_id = ?',
            [$user['id']],
        );
        return $context['db']->fetchAllAssociative(
            'SELECT * FROM servers WHERE id IN (?)',
            [$serverIds],
            [Connection::PARAM_INT_ARRAY]
        );
    }

    /**
     * Get a user's email
     * 
     * Requires that the requester is
     * authorized to view the user.
     * 
     * @param array $user The user being accessed
     * @param array $context The global context
     * 
     * @return string The user's email
     */
    public static function email(array $user, $_, $context): string
    {
        $context['auth']->assert('canViewUser', [$user['id']]);

        return $user['email'];
    }

    /**
     * Get a list of chats the user is a member of
     * 
     * Requires that the requester is
     * authorized to view the user.
     * 
     * @param array $user The user being accessed
     * @param array $context The global context
     * 
     * @return array Chats the user is a member of
     */
    public static function chats(array $user, $_, array $context): array
    {
        $context['auth']->assert('canViewUser', [$user['id']]);

        $chatIds = $context['db']->fetchFirstColumn(
            'SELECT chat_id FROM chats_users WHERE user_id = ?',
            [$user['id']],
        );
        return $context['db']->fetchAllAssociative(
            'SELECT * FROM chats WHERE id IN (?)',
            [$chatIds],
            [Connection::PARAM_INT_ARRAY]
        );    
    }

    /**
     * Get a list of messages the user has sent or received
     * 
     * Requires that the requester is
     * authorized to view the user.
     * 
     * @param array $user The user being accessed
     * @param array $context The global context
     * 
     * @return array Messages the user has sent or received
     */
    public static function messages(array $user, $_, array $context): array
    {
        $context['auth']->assert('canViewUser', [$user['id']]);

        $messageIds = $context['db']->fetchFirstColumn(
            'SELECT message_id FROM messages_users WHERE user_id = ?',
            [$user['id']],
        );
        return $context['db']->fetchAllAssociative(
            'SELECT * FROM messages WHERE id IN (?)',
            [$messageIds],
            [Connection::PARAM_INT_ARRAY]
        );
    }
}