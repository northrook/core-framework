<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use Core\Framework\Controller\ControllerAwareEvent;
use Core\Framework\Lifecycle\EventValidator;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use ReflectionClass, ReflectionException;

/**
 * {@see ControllerArgumentsEvent}
 *
 * Calls {@see Controller} methods annotated with {@see OnContent::class} or {@see OnDocument::class}.
 *
 * @internal
 *
 * @author Martin Nielsen <mn@northrook.com>
 *
 * @final  ✅
 */
final class ControllerActionInvoker extends ControllerAwareEvent
{
    use EventValidator;

    /**
     * @param ControllerArgumentsEvent $event
     */
    public function __invoke( ControllerArgumentsEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }

        dump( __METHOD__.' '.( $this->skip() ? 'true' : 'false') );

        try {
            ( new ReflectionClass( $this->controller ) )
                ->getMethod( 'controllerResponseMethods' )
                ->invoke( $this->controller );
        }
        catch ( ReflectionException $exception ) {
            $this->logger?->error(
                $exception->getMessage(),
                ['exception' => $exception],
            );
        }
    }
}
