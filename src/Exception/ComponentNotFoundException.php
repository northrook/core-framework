<?php

namespace Core\Framework\Exception;

use Core\Framework\View\Component;
use InvalidArgumentException;
use Throwable;

class ComponentNotFoundException extends InvalidArgumentException
{
    private readonly string $abstract;

    public function __construct(
        private readonly string $component,
        ?string                 $message = null,
        ?Throwable              $previous = null,
    ) {
        $this->abstract = Component::class;

        parent::__construct( $this->message( $message ), 500, $previous );
    }

    private function message( ?string $message = null ) : string
    {
        return $message ?? match ( true ) {
            ! \class_exists( $this->component )                    => "The class for the component '{$this->component}' does not exist.",
            ! \is_subclass_of( $this->component, $this->abstract ) => "The component class '{$this->component}' does not extend the Abstract {$this->abstract} class.",
            default                                                => "The {$this->component} Component was not found",
        };
    }
}
