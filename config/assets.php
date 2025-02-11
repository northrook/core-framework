<?php

// -------------------------------------------------------------------
// config\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Core\AssetManager;

return static function( AssetConfig $config ) : void {
    $config->register
        ->style(
            'core',
            ['/style/core.css', '/style/core/*.js'],
        )
        ->script(
            'core',
            '/script/core.js',
        )
        ->script(
            'htmx',
            '/script/htmx.js',
        );
};
