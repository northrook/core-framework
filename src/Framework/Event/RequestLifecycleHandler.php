<?php

declare(strict_types=1);

namespace Core\Framework\Event;

use Core\Framework\Lifecycle\LifecycleEvent;
use Core\Framework\Response\ResponseType;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\{ParameterBag, Request};
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @internal
 *
 * @see    RequestEvent
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
final class RequestLifecycleHandler extends LifecycleEvent
{
    private bool $isMainRequest;

    protected Request $request;

    protected ParameterBag $attributes;

    public function __invoke( RequestEvent $event ) : void
    {
        $profiler = $this->profiler?->event( 'request' );

        $this->isMainRequest = $event->isMainRequest();
        $this->request       = $event->getRequest();
        $this->attributes    = $this->request->attributes;

        self::$handleLifecycleEvent = $this->handleRequest();

        $this->setRequestLocale();

        $_view = $this->request->headers->has( 'hx-request' )
                ? ResponseType::CONTENT
                : ResponseType::DOCUMENT;

        // Set Request attributes
        $this->attributes->set( '_path', $this->request->getRequestUri() );
        $this->attributes->set( '_view', $_view );
        $this->attributes->set( 'hx-request', $_view === ResponseType::CONTENT );

        $profiler?->stop();
    }

    protected function setRequestLocale() : void
    {
        $_locale = $this->getValidLocale();

        $this->request->setLocale( $_locale );
        $this->attributes->set( '_locale', $_locale );

        $_params = $this->getRouteParameters();

        if ( isset( $_params['_locale'] ) ) {
            $_params['_locale'] = $_locale;
        }

        $this->attributes->set( '_route_params', $_params );
    }

    protected function handleRequest() : bool
    {
        // Do not parse sub-requests
        if ( $this->isMainRequest === false ) {
            $this->log(
                message : 'Lifecycle: Sub-request, skipping.',
                context : ['request' => $this->request],
                level   : 'debug',
            );
            return false;
        }

        // Only parse GET requests
        return $this->request->getMethod() === 'GET';
    }

    /**
     * @return array<string, ?string>
     */
    private function getRouteParameters() : array
    {
        return $this->attributes->get( '_route_params', [] );
    }

    private function getValidLocale() : string
    {
        $_locale = \trim( $this->request->getLocale(), " \n\r\t\v\0." );
        if ( \strlen( $_locale ) > 2 ) {
            $message = "Unknown locale: '{$_locale}'";
            throw new NotFoundHttpException( $message );
        }

        $_enabled = $this->getSetting(
            'site.enabled_locales',
            ['en', 'dk', 'nl'],
        );

        if ( ! \in_array( $_locale, $_enabled ) ) {
            $_locale = 'en';
            $this->log(
                message : 'Unknown locale: {_locale}',
                context : ['_locale' => $_locale],
                level   : 'warning',
            );
        }

        return $_locale;
    }
}
