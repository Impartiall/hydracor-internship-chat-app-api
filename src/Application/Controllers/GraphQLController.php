<?php

namespace App\Application\Controllers;

use App\Application\Settings\SettingsInterface;
use App\Auth\Authorization;
use App\Auth\Authentication;
use App\Exceptions\ClientSafeException;
use Doctrine\DBAL\Connection;
use GraphQL\Executor\Executor;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Utils\BuildSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Exception;
use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;

include_once(dirname(__DIR__, 3) . '/src/Exceptions/ClientSafeException.php');
include_once(dirname(__DIR__, 3) . '/src/Exceptions/errorCodes.php');

/**
 * A controller to handle GraphQL API requests
 */
class GraphQLController
{
    protected $db;
    protected $logger;

    /**
     * @var string $secret The secret key used for encoding and decoding JWTs
     */
    protected string $secret;

    public function __construct(Connection $connection, LoggerInterface $logger, SettingsInterface $settings)
    {
        $this->db = $connection;
        $this->logger = $logger;
        $this->jwt = $settings->get('jwt');
    }

    /**
     * The only controller action, since all GraphQL requests
     * are parsed and handled in the same way
     * 
     * @param Request $request The incoming request
     * @param Response $response The provided response object
     */
    public function index(Request $request, Response $response) {
        // Use custom GraphQL resolvers
        $this->setResolvers(include dirname(__DIR__, 3) . '/src/GraphQL/resolvers.php');
        
        // Load schema from file
        $schema = BuildSchema::build(file_get_contents(dirname(__DIR__, 3) . '/src/GraphQL/schema.graphqls'));

        // Get an authenticated user instance
        $authentication = new Authentication($this->jwt['secret'], $this->db);
        $user = $authentication->getAuthenticatedUser($request);

        // Read data passed in request
        $input = $request->getParsedBody();
        $query = $input['query'];

        $variables = isset($input['variables']) ? $input['variables'] : null;

        // Pass database connection and logger to resolvers
        $context = [
            'db'      => $this->db,
            'auth'    => new Authorization($user, $this->db),
            'logger'  => $this->logger,
            'jwt' => $this->jwt,
        ];

        // Set the root value to a value of type array
        $rootValue = [];

        // Use error code in error format
        $errorFormatter = function(Error $error) {
            $formattedError = FormattedError::createFromException($error);
            $components = explode(ERROR_CODE_MESSAGE_SEPARATOR, $error->getMessage());
            if (isset($components[1]) && $error->isClientSafe()) {
                $formattedError['message'] = $components[1];
                $formattedError['code'] = $components[0];
            } else {
                $formattedError['code'] = INTERNAL;
            }
            return $formattedError;
        };

        $result = GraphQL::executeQuery($schema, $query, $rootValue, $context, $variables)
            ->setErrorFormatter($errorFormatter);

        // Generate response from query result
        $response->getBody()->write(json_encode($result));

        // Log SQL queries
        $sqlQueryLogger = $this->db->getConfiguration()->getSQLLogger();
        $this->logger->info(json_encode($sqlQueryLogger->queries));

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Set GraphQL resolver functions from an array
     * 
     * @param array $resolvers A map of GraphQL resolver functions
     */
    private function setResolvers(array $resolvers) {
        Executor::setDefaultFieldResolver(function ($source, $args, $context, ResolveInfo $info) use ($resolvers) {
            $fieldName = $info->fieldName;
    
            if (is_null($fieldName)) {
                throw new Exception('Could not get $fieldName from ResolveInfo');
            }
    
            if (is_null($info->parentType)) {
                throw new Exception('Could not get $parentType from ResolveInfo');
            }
    
            $parentTypeName = $info->parentType->name;
    
            if (isset($resolvers[$parentTypeName])) {
                $resolver = $resolvers[$parentTypeName];
    
                if (is_array($resolver)) {
                    if (array_key_exists($fieldName, $resolver)) {
                        $value = $resolver[$fieldName];
    
                        return is_callable($value) ? $value($source, $args, $context, $info) : $value;
                    }
                }
    
                if (is_object($resolver)) {
                    if (isset($resolver->{$fieldName})) {
                        $value = $resolver->{$fieldName};
    
                        return is_callable($value) ? $value($source, $args, $context, $info) : $value;
                    }
                }
            }
    
            return Executor::defaultFieldResolver($source, $args, $context, $info);
        });
    }
}