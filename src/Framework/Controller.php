<?php

declare(strict_types=1);

namespace Core\Framework;

use Core\Profiler\Interface\Profilable;
use Core\Profiler\ProfilerTrait;
use Symfony\Component\HttpFoundation\{Request, Response};
use Core\Framework\Controller\ResponseMethods;
use Core\Framework\Controller\Attribute\{OnContent, OnDocument};
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait};
use Core\Symfony\DependencyInjection\{ServiceContainer, SettingsAccessor};
use Core\Symfony\Interface\ServiceContainerInterface;
use Exception, RuntimeException, ReflectionClass, ReflectionException;

abstract class Controller implements ServiceContainerInterface, Profilable, LoggerAwareInterface
{
    use ServiceContainer,
        SettingsAccessor,
        ProfilerTrait,
        ResponseMethods,
        LoggerAwareTrait;

    protected Request $request;

    final public function setCurrentRequest( Request $request ) : void
    {
        $this->request = $request;
    }

    final protected function response( ?string $content = null ) : Response
    {
        return new Response( $content );
    }

    /**
     * @return void
     */
    final protected function controllerResponseMethods() : void
    {
        if ( $this->request->attributes->has( '_controller_actions' ) ) {
            return;
        }

        // Add invoked methods to the Request attributes
        $responseType = $this->isHtmxRequest()
                ? OnContent::class
                : OnDocument::class;

        $calledMethods = [];

        foreach ( ( new ReflectionClass( $this ) )->getMethods() as $method ) {
            if ( ! $method->getAttributes( $responseType ) ) {
                continue;
            }

            $action = $method->getName();

            $this->profiler?->event( $action );

            $parameters = [];

            // Locate requested services
            foreach ( $method->getParameters() as $parameter ) {
                $injectableClass = $parameter->getType()?->__toString();

                \assert( \is_string( $injectableClass ) );

                try {
                    $argument = $this->serviceLocator->get( $injectableClass );
                    \assert( \is_object( $argument ) );
                    $calledMethods[$action][] = $argument::class;
                    $parameters[]             = $argument;
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

            $this->profiler?->stop( $action );
        }

        $this->request->attributes->set( '_controller_actions', $calledMethods );
    }
}
