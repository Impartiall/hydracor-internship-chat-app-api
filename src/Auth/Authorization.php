<?php

namespace App\Auth;

use App\Exceptions\AuthorizationException;

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
            if (is_null($this->getRequester())) {
                throw new AuthorizationException('This action requires authorization but no author was found.');
            } else {
                $username = $this->getRequester()['username'];
                throw new AuthorizationException("User `$username` is not authorized for this action.");
            }
        }
    }
    
    /**
     * Validate whether a requester can view private properties on a user
     * 
     * This authorization requires that the requester is the user being accessed.
     * 
     * @param int $userId The id of the user the requester is trying to view
     * 
     * @return bool Whether or not the user is authorized for this action
     */
    public function isAuthForUser(int $userId): bool
    {
        return ($this->getRequester()['id'] ?? null) === $userId;
    }
}
