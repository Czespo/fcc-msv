<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder)
{
    // Global Settings Object
    $containerBuilder->addDefinitions(
    [
        SettingsInterface::class => function ()
        {
            // Include private configuration.
            $private_config = include(__DIR__ . '/../private/config.php');
            $settings = [
                'displayErrorDetails' => true, // Should be set to false in production
                'logError'            => true,
                'logErrorDetails'     => true,
                'logger' => [
                    'name' => 'fcc-msv',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG
                ]
            ];

            return new Settings($private_config + $settings);
        }
    ]);
};
