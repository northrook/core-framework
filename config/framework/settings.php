<?php

// -------------------------------------------------------------------
// config\framework\settings
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\SettingsProvider;

return static function( ContainerConfigurator $container ) : void {
    $scheme = (string) ( $_SERVER['REQUEST_SCHEME'] ?? '' );
    $domain = (string) ( $_SERVER['SERVER_NAME'] ?? '' );

    $timezone = (string) ( $_SERVER['SERVER_TIMEZONE'] ?? 'UTC' );

    // Default Settings
    $default = [
        // System
        'charset'     => CHARSET,
        'env'         => param( 'kernel.environment' ),
        'debug'       => param( 'kernel.debug' ),
        'maintenance' => false,

        // Site Basics
        'site.name'        => 'Framework',
        'site.description' => null,
        'site.url'         => "{$scheme}://{$domain}",
        'site.domain'      => $domain,
        'site.public'      => false,
        'site.timezone'    => $timezone,

        // Locales
        'locale'           => param( 'kernel.default_locale' ),
        'locale.supported' => param( 'kernel.enabled_locales' ),
        'locale.detect'    => true,

        // Cache
        'cache.enabled' => true,
        'cache.ttl'     => 14_400,
    ];

    $container->parameters()->set( 'core.settings.default', $default );

    $container->services()
        ->set( 'core.settings_provider', SettingsProvider::class )
        ->tag( 'monolog.logger', ['channel' => 'settings'] )
        ->args(
            [
                '%dir.var%/settings.php',
                $default,
                true,
            ],
        );
};
