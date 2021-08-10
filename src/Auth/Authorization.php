<?php

namespace App\Auth;

use Doctrine\DBAL\Connection;

/**
 * A grouping of methods for authorizing API access
 */
class Authorization
{
    /**
     * @var array|null $requester The user array representing the
     * authenticated user making the request
     */
    protected array $requester;

    /**
     * @var Connection $connection A database connection
     */
    protected Connection $connection;

    /**
     * Create an instance of the Authorization class from an authenticated user
     * 
     * @param array|null The user array representing the authenticated user making the request
     */
    public function __construct(array $user, Connection $connection)
    {
        $this->requester = $user;
    }

    /**
     * Validate whether a requester can view private properties on a user
     * 
     * This authorization requires that the requester is the user being accessed
     * 
     * @param int $userId The id of the user the requester is trying to view
     * 
     * @return bool Whether or not the requester is authorized for this action
     */
    public function canViewUser(int $userId): bool
    {
        return ($this->requester['id'] ?? null) === $userId;
    }

    /**
     * Validate whether a requester can view private properties on a chat
     * 
     * This authorization requires that the requester is
     * a member of the chat being accessed
     * 
     * @param int $chatId The id of the chat the requester is trying to view
     * 
     * @return bool Whether or not the requester is authorized for this action
     */
    public function canViewChat(int $chatId): bool
    {
        $memberUserIds = $this->connection->fetchFirstColumn(
            'SELECT user_id FROM chats_users WHERE chat_id = ?',
            [$chatId]
        );
        return in_array($this->requester['id'], $memberUserIds);
    }
}
