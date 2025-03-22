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
    use ResponseMethods,
        ServiceContainer,
        SettingsAccessor,
        StopwatchProfiler,
        LoggerAwareTrait;

    protected Request $request;

    final public function setProfiler( ?Stopwatch $stopwatch, ?string $category = 'Controller' ) : void
    {
        $this->assignProfiler( $stopwatch, $category );
    }

    /**
     * Set by {@see ControllerActionInvoker::__invoke}.
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
     * Called by {@see Event\ControllerMethodInvoker::controllerOnViewMethods}.
     *
     * @return void
     */
    final protected function controllerResponseMethods() : void
    {
        if ( $this->request->attributes->has( '_controller_actions' ) ) {
            return;
        }

        // Add invoked methods to the Request attributes
        $responseType = $this->request->attributes->get( 'htmx', false )
                ? OnContent::class
                : OnDocument::class;

        $calledMethods = [];

        // Loop through each Controller::method
        foreach ( ( new ReflectionClass( $this ) )->getMethods() as $method ) {
            // Only parse methods with a OnView attribute
            if ( ! $method->getAttributes( $responseType ) ) {
                continue;
            }

            $action     = $method->getName();
            $profiler   = $this->profiler?->event( $action );
            $parameters = [];

            // Locate requested services arguments
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

            $profiler?->stop();
        }

        $this->request->attributes->set( '_controller_actions', $calledMethods );
    }
}
