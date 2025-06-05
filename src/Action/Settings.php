<?php

declare(strict_types=1);

namespace Core\Action;

use Core\Compiler\Autodiscover;
use Core\Interface\{ActionInterface, SettingsInterface};

#[Autodiscover( autowire : true )]
final readonly class Settings implements ActionInterface
{
    public function __construct( private SettingsInterface $settings ) {}

    /**
     * Get a setting by its key.
     *
     * If no setting is found, but a valid `set` key and `value` is provided, and given the current `user` has relevant permissions, the Setting will be set and saved.
     *
     * @template T_Setting of null|array<array-key, scalar>|scalar
     *
     * @param non-empty-string $setting
     * @param T_Setting        $default
     *
     * @return T_Setting
     */
    public function __invoke(
        string $setting,
        mixed  $default,
    ) : mixed {
        return $this->settings->get( $setting, $default );
    }

    /**
     * Check if a given Setting is defined.
     *
     * @param non-empty-string $setting
     *
     * @return bool
     */
    public function has( string $setting ) : bool
    {
        return $this->settings->has( $setting );
    }

    /**
     * Get a setting by its key.
     *
     * If no setting is found, but a valid `set` key and `value` is provided, and given the current `user` has relevant permissions, the Setting will be set and saved.
     *
     * @template T_Setting of null|array<array-key, scalar>|scalar
     *
     * @param non-empty-string $setting
     * @param T_Setting        $default
     *
     * @return T_Setting
     */
    public function get(
        string $setting,
        mixed  $default,
    ) : mixed {
        return $this->settings->get( $setting, $default );
    }
}
