<?php

declare(strict_types=1);

namespace Core\Framework\DependencyInjection;

use Core\Framework\Response\{Document, Headers, Parameters};
use Core\Service\{Pathfinder, Request};
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @property-read Request               $request
 * @property-read Pathfinder            $pathfinder
 * @property-read Document              $document
 * @property-read Parameters            $parameters
 * @property-read Headers               $headers
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
trait ServiceContainer
{
    protected readonly ServiceLocator $serviceLocator;

    public function __get( string $service )
    {
        return match ( $service ) {
            'request'        => $this->serviceLocator( Request::class ),
            'pathfinder'     => $this->serviceLocator( Pathfinder::class ),
            'document'       => $this->serviceLocator( Document::class ),
            'parameters'     => $this->serviceLocator( Parameters::class ),
            'headers'        => $this->serviceLocator( Headers::class ),
            'serviceLocator' => $this->serviceLocator( ServiceLocator::class ),
        };
    }

    #[Required]
    final public function setServiceLocator( ServiceLocator $serviceLocator ) : void
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @template Service
     *
     * @param class-string<Service> $get
     *
     * @return Service
     */
    final protected function serviceLocator( string $get ) : mixed
    {
        return $this->serviceLocator->get( $get );
    }
}
