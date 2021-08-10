<?php

namespace App\Auth;

/**
 * A grouping of methods for authorizing API access
 */
class Authorization
{
    /**
     * @var array|null The user array representing the authenticated user making the request
     */
    protected $requester;

    /**
     * Create an instance of the Authorization class from an authenticated user
     * 
     * @param array|null The user array representing the authenticated user making the request
     */
    public function __construct($user)
    {
        $this->requester = $user;
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
     * Validate whether a requester can view private properties on a user
     * 
     * This authorization requires that the requestor is the user being accessed
     * 
     * @param int $userId The id of the user the requestor is trying to view
     * 
     * @return bool Whether or not the user is authorized for this action
     */
    public function canViewUser(int $userId): bool
    {
        return ($this->requester['id'] ?? null) === $userId;
    }
}
