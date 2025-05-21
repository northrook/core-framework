<?php

namespace Core\Assets;

use Core\AssetManager\AbstractAsset;
use Core\AssetManager\Config\Asset;

/**
 */
#[Asset( '/script/core.js' )]
final class CoreScript extends AbstractAsset
{
    /**
     * Initialize serves as a runtime by {@see __invoke}.
     */
    protected function build() : void
    {
        dump( $this );
    }
}
