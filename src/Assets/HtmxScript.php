<?php

namespace Core\Assets;

use Core\Asset\Type;
use Core\AssetManager\{RegisteredAsset};

/**
 * @internal
 */
#[RegisteredAsset(
    Type::SCRIPT,
    '/scripts/htmx.js',
)]
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
