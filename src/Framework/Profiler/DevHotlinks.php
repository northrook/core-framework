<?php

namespace Core\Framework\Profiler;

use Override;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Throwable;
use Symfony\Component\HttpFoundation\{Request, Response};

final class DevHotlinks extends AbstractDataCollector
{
    #[Override]
    public static function getTemplate() : string
    {
        return '@Core/profiler/hotlinks.html.twig';
    }

    #[Override]
    public function collect( Request $request, Response $response, ?Throwable $exception = null ) : void
    {
        $requestAssets = $request->headers->get( 'X-Request-Assets', 'core, document' );
        if ( $requestAssets ) {
            $requestAssets = \explode( ',', $requestAssets );
        }

        $this->data['links'] = [];

        $host = (string) $request->get(
            '_host',
            $request->getHost(),
        );

        $this
            ->addLink( 'Home', '/' )
            ->addLink( 'Demo', '/demo' )
            ->addLink( 'Welcome', '/welcome' )
            ->addLink( 'Onboarding', '/onboarding' )
            ->addLink( 'Admin', "https://admin.{$host}", 'admin.' );
    }

    protected function addLink(
        string  $label,
        string  $href,
        ?string $path = null,
    ) : self {
        $this->data['links'][$label] = [
            'label' => $label,
            'href'  => $href,
            'path'  => $path ?? $href,
        ];

        return $this;
    }

    public function links() : array
    {
        return $this->data['links'] ?? [];
    }
}
