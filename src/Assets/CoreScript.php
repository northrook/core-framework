<?php

namespace Core\Assets;

use Core\AssetManager\Config\Asset;

/**
 * @internal
 */
#[Asset( '/script/core.js' )]
final class CoreScript extends StyleAsset
{
    /**
     * :: __construct is handled by each extending class
     * .. Autowired by the DependencyInjection extension
     *
     * Initialize serves as a runtime __construct hook.
     *
     * @return $this
     */
    protected function initialize() : self
    {
        dump( $this );
        return $this;
    }
}
