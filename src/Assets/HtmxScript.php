<?php

namespace Core\Assets;

use Core\AssetManager\Config\Asset;

/**
 * @internal
 */
#[Asset( '/script/htmx.js' )]
final class HtmxScript extends ScriptAsset
{
    /**
     * Initialize serves as a runtime by {@see __invoke}.
     */
    protected function build() : void
    {
        dump( $this );
    }
}
