<?php

declare(strict_types=1);

namespace Core\AssetManager;

return static function( AssetConfig $config ) : void {
    $config->register
        ->imageDirectory(
            \dirname( __DIR__, 1 ).'/assets/images',
        );
};
