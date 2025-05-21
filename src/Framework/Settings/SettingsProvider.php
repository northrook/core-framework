<?php

declare(strict_types=1);

namespace Core\Framework\Settings;

use _Dev\Attribute\Experimental;
use Core\Exception\NotSupportedException;
use Core\Interface\SettingsProviderInterface;

/**
 * @internal
 */
#[Experimental]
final class SettingsProvider implements SettingsProviderInterface
{
    /** @var array<string, null|array<array-key, scalar>|scalar> */
    protected array $map = [];

    /** @var array<string, Setting> */
    protected array $settings = [];

    /**
     * @param string                                              $directory
     * @param array<string, null|array<array-key, scalar>|scalar> $defaults
     */
    public function __construct(
        private readonly string $directory,
        private readonly array  $defaults,
    ) {
        if ( ! \is_dir( $this->directory ) ) {
            \mkdir( $this->directory, 0777, true );
        }
        $this->map = \array_merge( $this->defaults );
    }

    /**
     * Checks both the `map` and `settings` repositories.
     *
     * @param string $setting
     *
     * @return bool
     */
    public function has( string $setting ) : bool
    {
        \assert( $this->validateKey( $setting ) );
        return \array_key_exists( $setting, $this->map ) || \array_key_exists( $setting, $this->settings );
    }

    public function get(
        string                           $setting,
        float|array|bool|int|string|null $default,
    ) : null|array|bool|float|int|string {
        \assert( $this->validateKey( $setting ) );

        if ( \array_key_exists( $setting, $this->map ) ) {
            return $this->map[$setting];
        }
        $method = __METHOD__;
        // dump( \get_defined_vars() );
        return $this->map[$setting] = $default;
    }

    public function set( string $setting, mixed $set ) : self
    {
        \assert( $this->validateKey( $setting ) );
        $this->map[$setting] = $set;

        $method = __METHOD__;
        // dump( \get_defined_vars() );

        return $this;
    }

    public function add( string $setting, mixed $add ) : self
    {
        \assert( $this->validateKey( $setting ) );
        $this->map[$setting] ??= $add;

        $method = __METHOD__;
        // dump( \get_defined_vars() );

        return $this;
    }

    public function all() : array
    {
        \assert(
            ( function() : bool {
                $map      = \array_keys( $this->map );
                $settings = \array_keys( $this->settings );
                // dump( \get_defined_vars() );
                \ksort( $settings );
                \ksort( $map );
                // dump( \get_defined_vars() );

                return $map === $settings;
            } )(),
        );
        // Validate `$map` contains all `$settings`.
        return $this->map;
    }

    public function reset() : void
    {
        throw new NotSupportedException( __METHOD__.' not implemented yet.' );
    }

    private function getPath( string $fileName ) : string
    {
        return $this->directory.'/'.$fileName;
    }

    private function validateKey( string $key ) : bool
    {
        return \ctype_alnum( \str_replace( ['.', '_'], '', $key ) ) && $key === \strtolower( $key );
    }
}
