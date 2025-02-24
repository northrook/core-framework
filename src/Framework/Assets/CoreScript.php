<?php

namespace Core\Framework\Assets;

use Core\Asset\Script;
use Core\AssetManager\Compiler\AssetService;
use InvalidArgumentException;
use Core\AssetManager\Interface\{AssetInterface, AssetServiceInterface};
use Psr\Log\{LoggerAwareInterface, LoggerInterface};
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @internal
 */
#[AssetService( 'script.core' )]
final readonly class CoreScript implements AssetServiceInterface, LoggerAwareInterface
{
    private ?LoggerInterface $logger;

    #[Required]
    public function setLogger( ?LoggerInterface $logger ) : void
    {
        $this->logger = $logger;
    }

    public function __invoke( AssetInterface $asset ) : AssetInterface
    {
        if ( ! $asset instanceof Script ) {
            throw new InvalidArgumentException( 'Asset must be an instance of '.Script::class );
        }

        $asset->compile( true );

        $this->logger->info( $asset->minifier->getReport()->string );

        return $asset;
    }
}
