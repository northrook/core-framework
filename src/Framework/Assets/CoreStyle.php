<?php

declare(strict_types=1);

namespace Core\Framework\Assets;

use Core\AssetManager\Interface\{AssetInterface, AssetServiceInterface};
use Core\Asset\StyleAsset;
use Core\AssetManager\Compiler\AssetService;
use Northrook\DesignSystem;
use InvalidArgumentException;

/**
 * @internal
 */
#[AssetService( 'style.core' )]
final readonly class CoreStyle implements AssetServiceInterface
{
    private DesignSystem $designSystem;

    public function __construct(
        ?DesignSystem $designSystem = null,
    ) {
        $this->designSystem = $designSystem ?? new DesignSystem();
    }

    /**
     * @param AssetInterface $asset
     *
     * @return AssetInterface
     */
    public function __invoke( AssetInterface $asset ) : AssetInterface
    {
        if ( ! $asset instanceof StyleAsset ) {
            throw new InvalidArgumentException( 'Asset must be an instance of '.StyleAsset::class );
        }

        $styles = $this->designSystem->generateStyles();

        $asset->addSource( $styles, true );
        $asset->prefersInline();

        return $asset;
    }
}
