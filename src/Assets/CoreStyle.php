<?php

declare(strict_types=1);

namespace Core\Assets;

use Core\AssetManager\Compiler\Asset;
use Northrook\DesignSystem;

/**
 * @internal
 */
#[Asset( ['/style/core.css', '/style/core/*.css'] )]
final class CoreStyle extends ScriptAsset
{
    private DesignSystem $designSystem;

    public function __construct(
        ?DesignSystem $designSystem = null,
    ) {
        $this->designSystem = $designSystem ?? new DesignSystem();
    }

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
        $styles = $this->designSystem->generateStyles();

        $this->meta->addSource( $styles );
        $this->prefersInline();

        dump( $this );
        return $this;
    }
}
