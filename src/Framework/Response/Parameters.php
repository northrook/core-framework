<?php

declare(strict_types=1);

namespace Core\Framework\Response;

use Core\Interface\ActionInterface;

final class Parameters implements ActionInterface
{
    private ?object $object = null;

    /** @var array<string, mixed> */
    private array $parameters = [];

    public function __construct( mixed ...$set )
    {
        foreach ( $set as $key => $parameter ) {
            \assert( \is_string( $key ), __METHOD__.' requires named arguments.' );

            $this->set( $key, $parameter );
        }
    }

    public function __invoke( mixed ...$parameters ) : self
    {
        foreach ( $parameters as $key => $parameter ) {
            \assert( \is_string( $key ), __METHOD__.' requires named arguments.' );

            $this->add( $key, $parameter );
        }

        return $this;
    }

    /**
     * Use an object as the TemplateType parameter.
     *
     * This excludes using array $parameters for the template.
     *
     * @param object $object
     *
     * @return void
     */
    public function use( object $object ) : void
    {
        $this->object = $object;
    }

    public function add( string $key, mixed $value ) : Parameters
    {
        $this->parameters[$key] ??= $value;
        return $this;
    }

    public function set( string $key, mixed $value ) : self
    {
        $this->parameters[$key] = $value;
        return $this;
    }

    public function has( string $key ) : bool
    {
        return \array_key_exists( $key, $this->parameters );
    }

    public function get( string $key ) : mixed
    {
        return $this->parameters[$key] ?? null;
    }

    /**
     * @internal
     * @return array<string, mixed>|object
     */
    public function resolve() : object|array
    {
        // TODO : handle array->object
        return $this->object ?? $this->parameters;
    }
}
