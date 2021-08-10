<?php

namespace App\GraphQL\Resolvers;

use Doctrine\DBAL\Connection;

/**
 * A collection of resolver methods for the Chat type
 */
class ChatResolver
{
    /**
     * Get a chat's id
     * 
     * @param array $chat The chat being accessed
     * 
     * @return array The chat's id
     */
    public static function id(array $chat, $_, $__): int
    {
        return $chat['id'];
    }

    /**
     * Get a chat's name
     * 
     * Requires that the requester is authorized
     * as a member of the chat
     * 
     * @param array $chat The chat being accessed
     * @param array $context The global context
     * 
     * @return array|null The chat's name
     */
    public static function name(array $chat, $_, array $context)
    {
        if (!$context['auth']->canViewChat($chat['id'])) return null;

        return $chat['name'];
    }

    /**
     * Get all the members of a chat
     * 
     * Requires that the requester is authorized
     * as a member of the chat
     * 
     * @param array $chat The chat being accessed
     * @param array $context The global context
     * 
     * @return array|null The chat's members
     */
    public static function users(array $chat, $_, array $context)
    {
        if (!$context['auth']->canViewChat($chat['id'])) return null;

        $userIds = $context['db']->fetchFirstColumn(
            'SELECT user_id FROM chats_users WHERE chat_id = ?',
            [$chat['id']]
        );
        return $context['db']->fetchAllAssociative(
            'SELECT * FROM users WHERE id IN (?)',
            [$userIds],
            [Connection::PARAM_INT_ARRAY]
        );
    }

    /**
     * Get all the messages sent in a chat
     * 
     * Requires that the requester is authorized
     * as a member of the chat
     * 
     * @param array $chat The chat being accessed
     * @param array $context The global context
     * 
     * @return array|null The chat's messages
     */
    public static function messages(array $chat, $_, array $context)
    {
        if (!$context['auth']->canViewChat($chat['id'])) return null;

        return $context['db']->fetchAllAssociative(
            'SELECT * FROM messages WHERE chat_id = ?',
            [$chat['id']]
        );
    }
}
