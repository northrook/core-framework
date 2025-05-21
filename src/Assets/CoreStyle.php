<?php

declare(strict_types=1);

namespace Core\Assets;

use Core\AssetManager\AbstractAsset;
use Core\AssetManager\Config\Asset;
use Core\Compiler\Hook\OnBuild;

/**
 * @internal
 */
#[Asset( ['/style/core.css', '/style/core/*.css'] )]
final class CoreStyle extends AbstractAsset
{
    #[OnBuild]
    protected function generateStyles() : self
    {
        dump( __METHOD__.' called.' );
        return $this;
    }

    /**
     * Initialize serves as a runtime by {@see __invoke}.
     */
    protected function build() : void
    {
        dump( __METHOD__.' called.' );
        dump( $this );
    }
}
