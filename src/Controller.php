<?php

declare(strict_types=1);

namespace Core\Framework;

use Core\Framework\Controller\{ContentResponse, DocumentResponse, ResponseMethods};
use Core\Framework\DependencyInjection\ServiceContainer;
use Core\Framework\DependencyInjection\ServiceContainerInterface;
use Core\Framework\Response\{Document, Headers, Parameters};
use Core\Service\{Pathfinder, Request};
use Northrook\Logger\Log;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller implements ServiceContainerInterface
{
    use ServiceContainer, ResponseMethods;

    /**
     * @return Request
     */
    final protected function getRequest() : Request
    {
        return $this->serviceLocator->get( Request::class );
    }

    final protected function response( ?string $content = null ) : Response
    {
        $this->controllerResponseMethods();
        return new Response( $content );
    }

    /**
     * @return void
     */
    private function controllerResponseMethods() : void
    {
        $responseType = $this->getRequest()->isHtmx ? ContentResponse::class : DocumentResponse::class;

        $autowire = [
            Headers::class,
            Parameters::class,
            Document::class,
            Pathfinder::class,
        ];

        foreach ( ( new ReflectionClass( $this ) )->getMethods() as $method ) {
            if ( ! $method->getAttributes( $responseType ) ) {
                continue;
            }

            $parameters = [];

            // Locate requested services
            foreach ( $method->getParameters() as $parameter ) {

                $injectableClass = $parameter->getType()?->__toString();

                \assert( \is_string( $injectableClass ) );

                if ( \in_array( $injectableClass, $autowire, true ) ) {
                    $parameters[] = $this->serviceLocator->get( $injectableClass );
                }
                else {
                    // TODO : Ensure appropriate exception is thrown on missing dependencies
                    //        nullable parameters will not throw; log in [dev], ignore in [prod]
                    dump( $method );
                }
            }

            // Inject requested services
            try {
                $method->invoke( $this, ...$parameters );
            }
            catch ( ReflectionException $e ) {
                Log::exception( $e );

                continue;
            }
        }
    }
}
