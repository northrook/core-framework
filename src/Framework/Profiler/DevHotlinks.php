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

        $host = $request->getHttpHost();

        $links = [
            'home' => [
                'href' => '/',
            ],
            'welcome' => [
                'href' => '/welcome',
            ],
            'onboarding' => [
                'href' => '/onboarding',
            ],
            'demo' => [
                'href' => '/demo',
            ],
            'admin' => [
                'href' => "https://admin.{$host}",
            ],
        ];

        $this->data = ['links' => $links];
    }

    public function links() : array
    {
        return $this->data['links'] ?? [];
    }
}
