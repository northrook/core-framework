<?php

declare(strict_types=1);

namespace Core\Framework\Assets;

use Core\Assets\Factory\Asset\StyleAsset;
use Core\Assets\Factory\Compiler\AssetArgument;

/**
 * @internal
 */
final class CoreStyle extends AssetArgument
{
    public static function filter( StyleAsset $model ) : StyleAsset
    {
        $ds = new \Northrook\DesignSystem();

        $styles = $ds->generateStyles();

        $model->addSource( $styles, true );

        $model->prefersInline();

        return $model;
    }
}
