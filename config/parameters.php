<?php

// -------------------------------------------------------------------
// config\framework\parameters
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Symfony\DependencyInjection\SettingsProvider;
use function Support\normalize_path;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
        ->set( SettingsProvider::class );

    foreach ( [
        'dir.root'          => '%kernel.project_dir%',
        'dir.config'        => '%dir.root%/config',
        'dir.var'           => '%dir.root%/var',
        'dir.public'        => '%dir.root%/public',
        'dir.public.assets' => '%dir.root%/public/assets',

        // Assets
        'dir.assets'        => '%dir.root%/assets',
        'dir.assets.build'  => '%dir.root%/assets/build',
        'dir.assets.themes' => '%dir.core%/assets',
        'dir.assets.cache'  => __DIR__.'/var/assets',

        // Core
        'dir.core'        => [__DIR__, 1],
        'dir.core.src'    => '%dir.core%/src',
        'dir.core.config' => '%dir.core%/config',
        'dir.core.assets' => '%dir.core%/assets',

        //
        'path.asset_manifest'   => '%dir.root%/var/asset.manifest',
        'path.pathfinder_cache' => '%dir.root%/var/pathfinder.cache',

        // Templates
        'dir.templates'      => '%dir.root%/templates',
        'dir.core.templates' => '%dir.core%/templates',

        // Cache
        'dir.cache'       => '%kernel.cache_dir%',
        'dir.cache.latte' => '%kernel.cache_dir%/latte',
        'dir.cache.view'  => '%kernel.cache_dir%/view',

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
