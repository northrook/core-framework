<?php

declare(strict_types=1);

namespace Core\Framework\Assets;

use Core\AssetManager\Interface\{AssetInterface, AssetServiceInterface};
use Northrook\DesignSystem;

/**
 * @internal
 */
final readonly class CoreStyle implements AssetServiceInterface
{
    private DesignSystem $designSystem;

    public function __construct(
        ?DesignSystem $designSystem = null,
    ) {
        $this->designSystem = $designSystem ?? new DesignSystem();
    }

    public function __invoke( AssetInterface $asset ) : AssetInterface
    {
        $styles = $this->designSystem->generateStyles();

        // $model->addSource( $styles, true );
        //
        // $model->prefersInline();

        return $asset;
    }
}
