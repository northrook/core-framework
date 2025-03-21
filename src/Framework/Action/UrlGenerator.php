<?php

declare(strict_types=1);

namespace Core\Framework\Action;

use Core\Symfony\DependencyInjection\Autodiscover;
use Core\Interface\ActionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @TODO [md] Create dynamic route generators for current request
 */
#[Autodiscover( autowire : true )]
final readonly class UrlGenerator implements ActionInterface
{
    public function __construct( private UrlGeneratorInterface $urlGenerator ) {}

    final protected function generateRoutePath( string $name, array $parameters = [], bool $relative = false ) : string
    {
        return $this->urlGenerator->generate(
            $name,
            $parameters,
            $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH,
        );
    }

    final protected function generateRouteUrl( string $name, array $parameters = [], bool $relative = false ) : string
    {
        return $this->urlGenerator->generate(
            $name,
            $parameters,
            $relative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    /**
     * @param string                $name
     * @param array<string, string> $parameters
     * @param bool                  $relative
     * @param bool                  $asUrl
     *
     * @return string
     */
    public function __invoke(
        string $name,
        array  $parameters = [],
        bool   $relative = false,
        bool   $asUrl = false,
    ) : string {
        $referenceType = $asUrl
                ? ( $relative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL )
                : ( $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH );

        return $this->urlGenerator->generate(
            $name,
            $parameters,
            $referenceType,
        );
    }

    /**
     * @param string                $name
     * @param array<string, string> $parameters
     * @param bool                  $relative
     *
     * @return string
     */
    public function routePath( string $name, array $parameters = [], bool $relative = false ) : string
    {
        return $this->urlGenerator->generate(
            $name,
            $parameters,
            $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH,
        );
    }

    /**
     * @param string                $name
     * @param array<string, string> $parameters
     * @param bool                  $relative
     *
     * @return string
     */
    public function routeUrl( string $name, array $parameters = [], bool $relative = false ) : string
    {
        return $this->urlGenerator->generate(
            $name,
            $parameters,
            $relative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
