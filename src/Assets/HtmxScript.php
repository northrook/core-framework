<?php

namespace Core\Assets;

use Core\AssetManager\Compiler\Asset;

/**
 * @internal
 */
#[Asset( '/script/htmx.js' )]
final class HtmxScript extends ScriptAsset
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
