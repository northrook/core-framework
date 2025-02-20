<?php

// -------------------------------------------------------------------
// config\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Core\AssetManager;

use Core\Framework\Assets\CoreStyle;

return static function( AssetConfig $config ) : void {
    $config->register
        ->style(
            'core',
            ['/styles/core.css', '/styles/core/*.css'],
            CoreStyle::class,
        )
        ->script(
            'core',
            '/scripts/core.js',
        )
        ->script(
            'htmx',
            '/scripts/htmx.js',
        );
};
