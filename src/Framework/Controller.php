<?php

declare(strict_types=1);

namespace Core\Framework;

use Symfony\Component\HttpFoundation\Response;
use Core\Framework\Controller\ResponseMethods;
use Core\Framework\Controller\Attribute\{OnContent, OnDocument};
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait};
use Core\Profiler\SettableStopwatchProfiler;
use Core\Profiler\Interface\SettableProfilerInterface;
use Core\Symfony\DependencyInjection\{ServiceContainer, SettingsAccessor};
use Core\Symfony\Interface\ServiceContainerInterface;
use Exception, RuntimeException, ReflectionClass, ReflectionException;

abstract class Controller implements ServiceContainerInterface, SettableProfilerInterface, LoggerAwareInterface
{
    use ServiceContainer,
        SettingsAccessor,
        SettableStopwatchProfiler,
        ResponseMethods,
        LoggerAwareTrait;

    final protected function response( ?string $content = null ) : Response
    {
        $this->controllerResponseMethods();
        return new Response( $content );
    }

    /**
     * @return void
     */
    final protected function controllerResponseMethods() : void
    {
        $this->profiler?->event( __METHOD__ );

        // Add invoked methods to the Request attributes
        $responseType = $this->isHtmxRequest()
                ? OnContent::class
                : OnDocument::class;

        foreach ( ( new ReflectionClass( $this ) )->getMethods() as $method ) {
            if ( ! $method->getAttributes( $responseType ) ) {
                continue;
            }

            $this->profiler?->event( $method->getName(), __METHOD__ );

            $parameters = [];

            // Locate requested services
            foreach ( $method->getParameters() as $parameter ) {
                $injectableClass = $parameter->getType()?->__toString();

                \assert( \is_string( $injectableClass ) );

                try {
                    $parameters[] = $this->serviceLocator->get( $injectableClass );
                }
                catch ( Exception $exception ) {
                    if ( ! $this->logger ) {
                        throw new RuntimeException( $exception->getMessage(), 500, $exception );
                    }

                    $this->logger->error( $exception->getMessage(), ['exception' => $exception] );
                }
            }

            // Inject requested services
            try {
                $method->invoke( $this, ...$parameters );
            }
            catch ( ReflectionException $exception ) {
                $this->logger?->error( $exception->getMessage(), ['exception' => $exception] );
            }

            $this->profiler?->stop( $method->getName() );
        }

        $this->profiler?->stop( __METHOD__, __METHOD__ );
    }
}
