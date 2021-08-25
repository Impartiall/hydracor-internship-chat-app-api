<?php

namespace App\Auth;

use App\Database\Records;
use App\Exceptions\AuthorizationException;
use Doctrine\DBAL\Connection;

/**
 * A grouping of methods for authorizing API access
 */
class Authorization
{
    /**
     * @var array|null $requester The user array representing the authenticated user making the request
     */
    protected $requester;

    /**
     * @var Connection $connection A database connection
     */
    protected Connection $connection;

    /**
     * Create an instance of the Authorization class
     * from an authenticated user and database connection
     * 
     * @param array|null $user The user array representing the authenticated user making the request
     * @param Connection $connection A database connection
     */
    public function __construct($user, Connection $connection)
    {
        $this->requester = $user;
        $this->connection = $connection;
    }

    /**
     * Accessor method for $this->requester
     * 
     * @return array|null The requester in this instance
     */
    public function getRequester()
    {
        return $this->requester;
    }

    /**
     * Throw a client readable authorization exception exception if authorization fails
     * 
     * @param string $callback The authorization method on which to assert success
     * @param array $args The arguments to pass to the authorization method
     * 
     * @throws AuthorizationException if the requester is not authorized
     */
    public function assert(string $callback, array $args): void
    {
        if (!call_user_func([$this, $callback], ...$args)) {
            if (!$this->isAuthorized()) {
                throw new AuthorizationException(AUTHOR_MISSING, 'This action requires authorization but no author was found.');
            } else {
                $username = $this->getRequester()['username'];
                throw new AuthorizationException(UNAUTHORIZED, "User `$username` is not authorized for this action.");
            }
        }
    }

    /**
     * Check whether a requester exists in this instance
     * 
     * @return bool True if the requester is not null, false otherwise
     */
    public function isAuthorized(): bool
    {
        return !is_null($this->getRequester());
    }

    /**
     * Validate whether a requester can edit properties on a user
     * 
     * This authorization requires that the requester is the user being accessed.
     * 
     * @param int $userId The id of the user the requester is trying to edit
     * 
     * @return bool Whether or not the requester is authorized for this action
     */
    public function canEditUser(int $userId): bool
    {
        return ($this->getRequester()['id'] ?? null) === $userId;
    }

    /**
     * Validate whether a requester can view private properties on a user
     * 
     * This authorization requires that the requester is the user being accessed.
     * 
     * @param int $userId The id of the user the requester is trying to view
     * 
     * @return bool Whether or not the requester is authorized for this action
     */
    public function canViewUser(int $userId): bool
    {
        return $this->canEditUser($userId);
    }

    /**
     * Validate whether a requester can edit properties on a server
     * 
     * This authorization requires that the requester is the owner of the server being accessed.
     * 
     * @param int $serverId The id of the server the requester is trying to edit
     * 
     * @return bool Whether or not the requester is authorized for this action
     */
    public function canEditServer(int $serverId): bool
    {
        $server = Records::selectById($this->connection, 'servers', $serverId);
        return ($this->getRequester()['id'] ?? null) === $server['owner_id'];
    }

    /**
     * Validate whether a requester can view private properties on a server
     * 
     * This authorization requires that the requester is a member of the server being accessed.
     * 
     * @param int $serverId The id of the server the requester is trying to view
     * 
     * @return bool Whether or not the requester is authorized for this action
     */
    public function canViewServer(int $serverId): bool
    {
        return Records::doesRelationshipExist(
            $this->connection,
            'servers_users',
            'server_id',
            $serverId,
            'user_id',
            $this->getRequester()['id'] ?? null
        );
    }

    /**
     * Validate whether a requester can edit properties on a chat
     * 
     * This authorization requires that the requester is a member of the chat being accessed.
     * 
     * @param int $chatId The id of the chat the requester is trying to edit
     * 
     * @return bool Whether or not the requester is authorized for this action
     */
    public function canEditChat(int $chatId): bool
    {
        return Records::doesRelationshipExist(
            $this->connection,
            'chats_users',
            'chat_id',
            $chatId,
            'user_id',
            $this->getRequester()['id'] ?? null
        );
    }

    /**
     * Validate whether a requester can view private properties on a chat
     * 
     * This authorization requires that the requester is a member of the chat being accessed.
     * 
     * @param int $chatId The id of the chat the requester is trying to view
     * 
     * @return bool Whether or not the requester is authorized for this action
     */
    public function canViewChat(int $chatId): bool
    {
        return $this->canEditChat($chatId);
    }
}
