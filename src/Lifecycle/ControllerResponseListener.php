<?php

namespace Core\Framework\Lifecycle;

use Core\Framework\Controller;
use Core\Framework\DependencyInjection\ServiceContainer;
use Symfony\Component\HttpKernel\Event\{ControllerEvent, ResponseEvent, ViewEvent};
use Northrook\Exception\E_Value;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\{Request, Response};
use Support\Reflect;
use function Support\{explode_class_callable, get_class_name};
use Stringable;
use LogicException;
use ReflectionException;

final class ControllerResponseListener
{
    use ServiceContainer;

    /**
     * For debugging - will be cached later.
     *
     * @var array<string, false|array{
     *      string: string,
     *      string: string
     *  }>
     */
    private array $responseTemplateCache = [];

    public function __construct() {}

    public function onKernelController( ControllerEvent $event ) : void
    {
        if ( ! $this->handleController( $event->getRequest() ) ) {
            return;
        }

        $isHtmx = $event->getRequest()->headers->has( 'hx-request' );

        $event->getRequest()->attributes->set( '_htmx_request', $isHtmx );
        $event->getRequest()->attributes->set( '_request_type', $isHtmx ? 'content' : 'document' );

        dump( $this );
    }

    public function onKernelView( ViewEvent $event ) : void
    {
        $event->setResponse( $this->resolveVewResponse( $event->getControllerResult() ) );
    }

    public function onKernelResponse( ResponseEvent $event ) : void
    {
        if ( ! $this->handleController( $event->getRequest() ) ) {
            return;
        }

        $responseTemplate = $this->resolveResponseTemplate( $event->getRequest() );
    }

    /**
     * Determine if the {@see Response} `$content` is a template.
     *
     * - Empty `$content` will use {@see Controller} attribute templates.
     * - If the `$content` contains no whitespace, and ends with `.latte`, it is a template
     * - All other strings will be considered as `text/plain`
     *
     * @param Request $request
     *
     * @return false|array{
     *     string: string,
     *     string: string
     * }
     */
    private function resolveResponseTemplate( Request $request ) : false|array
    {
        dump( $request );
        $caller = $request->attributes->get( '_controller' );

        \assert( \is_string( $caller ) );

        if ( isset( $this->responseTemplateCache[$caller] ) ) {
            return $this->responseTemplateCache[$caller];
        }

        [$controller, $method] = explode_class_callable( $caller, true );
        //
        // try {
        //     $reflectClass  = new ReflectionClass( $controller );
        //     $reflectMethod = new ReflectionMethod( $controller, $method );
        // }
        // catch ( ReflectionException $exception ) {
        //     throw new LogicException( $exception->getMessage() );
        // }

        $reflectClass  = Reflect::class( $controller );
        $reflectMethod = Reflect::method( $controller, $method );

        // ($classTemplate[0]->newInstance())->name;
        $controllerTemplate = Reflect::getAttribute( $reflectClass, Controller\Template::class );
        $methodTemplate     = Reflect::getAttribute( $reflectMethod, Controller\Template::class );

        // $template

        // Create a Support\Reflect helper for returning typed attributes

        dump(
            // $controller,
            $reflectClass->getAttributes(),
            $controllerTemplate,
            // $method,
            $reflectMethod->getAttributes(),
            $methodTemplate,
        );

        return false;
    }

    /**
     * Check if the passed {@see Request} is extending the {@see Controller}.
     *
     * @param Request $request
     *
     * @return bool
     */
    private function handleController( Request $request ) : bool
    {
        return \is_subclass_of( get_class_name( $request->attributes->get( '_controller' ) ), Controller::class );
    }

    private function resolveVewResponse( mixed $content ) : Response
    {
        if ( \is_string( $content ) || $content instanceof Stringable ) {
            $content = (string) $content;
        }

        if ( ! ( \is_string( $content ) || \is_null( $content ) ) ) {
            $content = E_Value::error(
                'Controller return value is {type}, the {Response} object requires {string}|{null}. {null} was provided instead.',
                ['type' => \gettype( $content )],
                throw: true,
            );
        }

        return new Response( $content ?: null );
    }
}
