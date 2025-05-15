<?php

declare(strict_types=1);

namespace Core\Assets;

use Core\AssetManager\Config\Asset;
use Core\Compiler\Hook\OnBuild;
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

    #[OnBuild]
    protected function generateStyles() : self
    {
        $styles = $this->designSystem->generateStyles();

        $this->meta->addSource( $styles );

        dump( $this );
        return $this;
    }
}
