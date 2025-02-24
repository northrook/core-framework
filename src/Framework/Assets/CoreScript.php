<?php

namespace Core\Framework\Assets;

use Core\Asset\Script;
use Core\AssetManager\Compiler\AssetService;
use InvalidArgumentException;
use Core\AssetManager\Interface\{AssetInterface, AssetServiceInterface};

/**
 * @internal
 */
#[AssetService( 'script.core' )]
final readonly class CoreScript implements AssetServiceInterface
{
    public function __invoke( AssetInterface $asset ) : AssetInterface
    {
        if ( ! $asset instanceof Script ) {
            throw new InvalidArgumentException( 'Asset must be an instance of '.Script::class );
        }

        $asset->compile( true );

        dump( $this, $asset );
        return $asset;
    }
}
