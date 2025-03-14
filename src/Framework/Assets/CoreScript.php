<?php

namespace Core\Framework\Assets;

use Core\Asset\ScriptAsset;
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
        if ( ! $asset instanceof ScriptAsset ) {
            throw new InvalidArgumentException( 'Asset must be an instance of '.ScriptAsset::class );
        }

        $asset->mergeImportStatements();

        return $asset;
    }
}
