<?php

namespace App\Auth;

use App\Database\Records;
use App\Exceptions\AuthenticationException;
use Doctrine\DBAL\Connection;
use Exception;
use Slim\Psr7\Request;
use ReallySimpleJWT\Token;

/**
 * A grouping of methods for authenticating API requests
 */
class Authentication
{
    /**
     * @var string $secret The secret key used to encode and decode the JWT
     */
    protected string $secret;

    /**
     * @var Connection $connection A database connection
     */
    protected Connection $connection;

    /**
     * Create an instance of the Authentication class
     * 
     * @param string $secret The secret key used to encode and decode the JWT
     * @param Connection $connection A database connection
     */
    public function __construct(string $secret, Connection $connection)
    {
        $this->secret = $secret;
        $this->connection = $connection;
    }

    /**
     * Return an authenticated user array based on a request
     * 
     * If the request has a JWT and the JWT validates to an existing user,
     * the array modeling that user will be returned. If the Authorization
     * header is not present, null will be returned.
     * 
     * @param Request $request The incoming request to read from
     * 
     * @return array|null The authenticated user, or null if there is no Authorization header
     */
    public function getAuthenticatedUser(Request $request)
    {
        $authHeader = $request->getHeaderLine('Authorization', '');
        $token = $this->getTokenFromHeader($authHeader);

        if (!$token) return null;

        if (!Token::validate($token, $this->secret))
            throw new AuthenticationException('Invalid JWT.');

        if (!Token::validateExpiration($token, $this->secret))
            throw new AuthenticationException('JWT is expired.');

        $payload = Token::getPayload($token, $this->secret);
        return Records::selectById($this->connection, 'users', $payload['user_id']);
    }

    /**
     * Read the JWT string from the Authorization HTTP header
     * 
     * If the header does not have a 'Bearer ' qualifier, null is returned.
     * 
     * @param string $authHeader The value of the HTTP Authorization header
     * 
     * @return string|null The JWT string with the 'Bearer ' qualifier removed
     */
    protected function getTokenFromHeader(string $authHeader)
    {
        define('BEARER', 'Bearer ');
        if (strpos($authHeader, BEARER) !== -1 && strlen($authHeader) >= 7) {
            return substr(
                $authHeader,
                strlen(BEARER),
                strlen($authHeader) - strlen(BEARER)
            );
        } else {
            return null;
        }
    }
}
