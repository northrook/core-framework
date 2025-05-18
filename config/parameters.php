<?php

// -------------------------------------------------------------------
// config\framework\parameters
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use function Support\normalize_path;

return static function( ContainerConfigurator $container ) : void {
    $container->parameters()->set( 'session.metadata.storage_key', '_session.meta' );

    // Directories
    foreach ( [
        'dir.root'          => '%kernel.project_dir%',
        'dir.cache'         => '%kernel.cache_dir%',
        'dir.config'        => '%dir.root%/config',
        'dir.var'           => '%dir.root%/var',
        'dir.temp'          => '%dir.root%/var/temp',
        'dir.public'        => '%dir.root%/public',
        'dir.public.assets' => '%dir.root%/public/assets',

        // Core
        'dir.core'        => [__DIR__, 1],
        'dir.core.src'    => '%dir.core%/src',
        'dir.core.config' => '%dir.core%/config',
        'dir.core.assets' => '%dir.core%/assets',

        // Templates
        'dir.templates'      => '%dir.root%/templates',
        'dir.core.templates' => '%dir.core%/templates',

        // Cache
        'dir.cache.latte'          => '%dir.cache%/latte',
        'dir.cache.view'           => '%dir.cache%/view',
        'dir.cache.view.component' => '%dir.cache%/view/component',

        // Themes
        'path.theme.core' => '%dir.core%/config/themes/core.php',

        // Settings DataStore
        'path.settings_store'   => '%dir.root%/var/settings/data_store.php',
        'path.settings_history' => '%dir.root%/var/settings/history_store.php',
    ] as $key => $value ) {
        if ( \is_array( $value ) ) {
            [$dir, $level] = $value;
            \assert(
                // : Asserts are here to _assert_, we cannot assume type safety
                // @phpstan-ignore-next-line
                \is_string( $dir ) && \is_int( $level ),
                'CoreBundle.config.parameters only accepts strings, or an array of [__DIR__, LEVEL]',
            );
            $value = \dirname( $dir, $level );
        }
        $container->parameters()->set( $key, normalize_path( $value ) );
    }
};
