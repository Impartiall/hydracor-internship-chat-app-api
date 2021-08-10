<?php
declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN) ?: false, // Should be set to false in production
                'logError'            => false,
                'logErrorDetails'     => false,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'db' => [
                    'name'     => 'postgres', // Database name
                    'host'     => 'db',
                    'username' => 'postgres',        
                    'password' => getenv('POSTGRES_PASSWORD') ?: 'password',
                    'driver'   => 'pdo_pgsql' 
                ],
                'jwt' => [
                    'secret' => getenv('JWT_SECRET') ?: 'sec!ReT423*&',
                    'lifetime' => getenv('JWT_LIFETIME') ?: 600, // Default JWT lifetime is 10 minutes
                ],
            ]);
        }
    ]);
};
