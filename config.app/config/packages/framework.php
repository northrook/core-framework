<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

return static function( FrameworkConfig $framework ) : void {

    $framework->secret( '%env(APP_SECRET)%' );

    $framework->session( ['enabled' => true] );

    $framework->fragments( ['enabled' => true] );
};
