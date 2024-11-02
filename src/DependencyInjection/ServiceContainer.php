<?php

declare(strict_types=1);

namespace Core\Framework\DependencyInjection;

use Core\Framework\DependencyInjection\Exception\ServiceContainerException;
use Northrook\Logger\Log;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\Service\Attribute\Required;
use Throwable;

/**
 * @author Martin Nielsen <mn@northrook.com>
 */
trait ServiceContainer
{
    protected readonly ServiceLocator $serviceLocator;

    /**
     * @final
     *
     * @param null|'debug'|'dev'|'prod'|'test' $is
     *
     * @return bool|string
     */
    final protected function applicationEnvironment( ?string $is = null ) : string|bool
    {
        $env   = (string) $this->parameterBag()->get( 'kernel.environment' );
        $debug = (bool) $this->parameterBag()->get( 'kernel.debug' );

        // Log a warning if debugging is enabled in production.
        if ( $debug && 'prod' === $env ) {
            Log::warning( '{Debug} enabled in production.' );
        }

        // Stand-alone debug check
        if ( 'debug' === $is && $debug ) {
            return true;
        }

        // True if the environment matches asked, or true if we are debugging anywhere but production
        if ( $env === $is || ( $is && 'prod' !== $env && $debug ) ) {
            return true;
        }

        // Return the environment string
        return $env;
    }

    final protected function parameterBag() : ParameterBagInterface
    {
        return $this->serviceLocator( ParameterBagInterface::class );
    }

    #[Required]
    final public function setServiceLocator( ServiceLocator $serviceLocator ) : void
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @final
     *
     * @template Service
     *
     * @param class-string<Service> $get
     * @param bool                  $nullable
     *
     * @return Service
     */
    final protected function serviceLocator( string $get, bool $nullable = false ) : mixed
    {
        try {
            return $this->serviceLocator->get( $get );
        }
        catch ( Throwable $exception ) {
            $exception = new ServiceContainerException( $get, previous : $exception );

            if ( ! $nullable ) {
                throw $exception;
            }

            if ( $this->applicationEnvironment( 'dev' ) ) {
                Log::exception( $exception );
            }

        }

        return null;
    }
}
