<?php

// -------------------------------------------------------------------
// config\framework\settings
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\SettingsProvider;

return static function( ContainerConfigurator $container ) : void {
    //
    $default = [];

    $container->parameters()->set( 'core.settings.default', $default );

    $container->services()
        ->set( 'core.settings_provider', SettingsProvider::class )
        ->tag( 'monolog.logger', ['channel' => 'settings'] )
        ->args( ['%dir.root%/assets', $default] );
};
