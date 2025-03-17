<?php

declare(strict_types=1);

namespace Core\Framework;

use Core\Framework\Response\{View, ViewResponse};
use Core\Profiler\Interface\Profilable;
use Core\Profiler\{StopwatchProfiler};
use Symfony\Component\HttpFoundation\{Request};
use Core\Framework\Controller\ResponseMethods;
use Core\Framework\Controller\Attribute\{OnContent, OnDocument};
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait};
use Core\Symfony\DependencyInjection\{ServiceContainer, SettingsAccessor};
use Core\Symfony\Interface\ServiceContainerInterface;
use Exception, RuntimeException, ReflectionClass, ReflectionException;
use Symfony\Component\Stopwatch\Stopwatch;
use InvalidArgumentException;
use const Support\AUTO;

abstract class Controller implements ServiceContainerInterface, Profilable, LoggerAwareInterface
{
    protected const string CATEGORY = 'Controller';

    use ServiceContainer,
        SettingsAccessor,
        StopwatchProfiler,
        ResponseMethods,
        LoggerAwareTrait;

    protected Request $request;

    final public function setProfiler( ?Stopwatch $stopwatch, ?string $category = null ) : void
    {
        $this->assignProfiler( $stopwatch, $this::CATEGORY );
    }

    /**
     * Set by {@see ControllerAwareEvent::resolveControllerEvent()}.
     *
     * @internal
     *
     * @param Request $request
     *
     * @return void
     */
    final public function setCurrentRequest( Request $request ) : void
    {
        $this->request = $request;
    }

    /**
     * @param null|string                      $content
     * @param null|int                         $status
     * @param array<string, list<null|string>> $headers
     *
     * @return ViewResponse
     */
    final protected function response(
        ?string $content = null,
        ?int    $status = AUTO,
        array   $headers = [],
    ) : ViewResponse {
        $view = $this->request->attributes->get( '_response' );

        if ( ! $view instanceof View ) {
            $message = "Expected a 'View::TYPE' on this 'ResponseEvent'.";
            throw new InvalidArgumentException( $message );
        }

        return new ViewResponse(
            $view,
            $content,
            $status,
            $headers,
        );
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
