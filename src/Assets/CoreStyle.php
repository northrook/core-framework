<?php

declare(strict_types=1);

namespace Core\Assets;

use Core\AssetManager\Assets\StyleAsset;
use Core\Compiler\Hook\OnBuild;

final class CoreStyle extends StyleAsset
{
    #[OnBuild]
    protected function generateStyles() : self
    {
        dump( __METHOD__.' called.' );
        return $this;
    }
}
