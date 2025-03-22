<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Controller\AdminController;
use Core\Interface\SettingsProviderInterface;
use Symfony\Component\Routing\Route;

/**
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html
 */
final class AdminRouteLoader extends RouteLoader
{
    public function __construct(
        ?string                                    $env,
        private readonly SettingsProviderInterface $settings,
    ) {
        parent::__construct( $env );
    }

    public function type() : string
    {
        return 'admin';
    }

    // @phpstan-ignore-next-line
    public function controller() : string|false
    {
        return AdminController::class;
    }

    protected function compile( mixed $resource, ?string $type ) : bool
    {
        $this->name( 'admin' )
            ->scheme( 'https' )
            ->method( 'GET' );

        if ( $this->settings->get( 'admin.access.sub_domain', true ) ) {
            $this
                ->path( '/' )
                ->host( 'admin.{domain}.{tld}' );
        }
        else {
            $this->path( '/admin' );
        }

        dump( $this->routes );
        return true;
    }
}
