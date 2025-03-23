<?php

namespace Core\Framework\Profiler;

use Override;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Throwable;
use Symfony\Component\HttpFoundation\{Request, Response};
use function Support\str_after;

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
        $this->data['links'] = [];

        $host = 'sf-temp.wip';

        $this
            ->addLink( 'Home', "https://{$host}/" )
            ->addLink( 'Demo', "https://{$host}/demo" )
            ->addLink( 'Welcome', "https://{$host}/welcome" )
            ->addLink( 'Onboarding', "https://{$host}/onboarding" )
            ->addLink( 'Admin', "https://admin.{$host}" );
    }

    protected function addLink(
        string  $label,
        string  $href,
        ?string $path = null,
    ) : self {
        $this->data['links'][$label] = [
            'label' => $label,
            'href'  => $href,
            'path'  => $path ?? \ltrim( str_after( $href, '//' ), '/' ),
        ];

        return $this;
    }

    public function links() : array
    {
        return $this->data['links'] ?? [];
    }
}
