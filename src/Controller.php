<?php

declare(strict_types=1);

namespace Core\Framework;

use Core\Framework\Attribute\{OnContent, OnDocument};
use Symfony\Component\HttpFoundation\{Response};
use Core\Framework\DependencyInjection\{ServiceContainer, ServiceContainerInterface};
use Core\Framework\Controller\ResponseMethods;
use Core\Framework\Response\{Document, Headers, Parameters};
use Northrook\Logger\Log;
use ReflectionClass;
use ReflectionException;
abstract class Controller implements ServiceContainerInterface
{
    use ServiceContainer, ResponseMethods;

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
        $responseType = $this->getRequest()->headers->has( 'HX-Request' ) ? OnContent::class : OnDocument::class;

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
