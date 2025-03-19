<?php

declare(strict_types=1);

namespace Core\Framework;

use Core\AssetManager;
use Core\AssetManager\Interface\MinifiedAssetInterface;
use Core\Framework\Response\Template;
use Core\View\{ComponentFactory, Document, DocumentEngine, TemplateEngine};
use Core\Interface\LazyService;
use Core\Profiler\{ProfilerTrait};
use Core\Profiler\Interface\{Profilable};
use Core\Symfony\ToastService;
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait};
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Stopwatch\Stopwatch;

class ResponseRenderer implements LazyService, Profilable, LoggerAwareInterface
{
    use ProfilerTrait, LoggerAwareTrait;

    protected ?string $content = null;

    public bool $clearTemplateCache = true;

    public function __construct(
        public readonly DocumentEngine   $documentEngine,
        public readonly TemplateEngine   $templateEngine,
        public readonly ComponentFactory $componentFactory,
        public readonly AssetManager     $assetManager,
        protected readonly Document      $document,
        protected readonly ToastService  $toastService,
    ) {}

    final public function setProfiler( ?Stopwatch $stopwatch, ?string $category = null ) : void
    {
        $this->assignProfiler( $stopwatch, 'View' );
    }

    final public function setResponseContent( ResponseEvent $event, ?Template $template = null ) : self
    {
        $this->content = (string) $event->getResponse()->getContent() ?: '';

        // If contains any whitespace, we can safely assume it not a template string
        if ( \str_contains( $this->content, ' ' ) ) {
            $this->documentEngine->setInnerHtml( $this->content );
            return $this;
        }

        // Handle manual template paths early
        if ( \str_ends_with( $this->content, '.latte' ) ) {
            $this->content = $this->templateEngine->render( $this->content );
            $this->documentEngine->setInnerHtml( $this->content );
            return $this;
        }

        if ( ! $this->content && ! $template ) {
            $this->logger?->error(
                '{route} expected a Template, but none was provided.',
                ['route' => $event->getRequest()->getRequestUri(), 'event' => $event],
            );
            return $this;
        }

        $contentOnly = $event->getRequest()->headers->has( 'hx-request' );

        $view = $contentOnly ? $template?->content : $template?->document;

        if ( ! $this->content && ! $view ) {
            $this->logger?->error(
                '{route} expected a Template, an object was provided, but no templates were set.',
                [
                    'route'    => $event->getRequest()->getRequestUri(),
                    'event'    => $event,
                    'template' => $template,
                ],
            );
            return $this;
        }

        if ( $view ) {
            $this->content = $this->templateEngine->render( $view );
        }

        $this->documentEngine->setInnerHtml( $this->content );

        return $this;
    }

    final public function getResponse() : Response
    {
        $this->handleEnqueuedAssets();
        // $this->handleToastMessages();
        $content = (string) $this->documentEngine;
        return new Response(
            $content,
        );
    }

    final protected function handleEnqueuedAssets() : void
    {
        foreach ( $this->document->assets->getEnqueuedAssets() as $assetKey ) {
            $profiler = $this->profiler?->event( $assetKey, 'Asset' );
            $asset    = $this->assetManager->getAsset( $assetKey );

            $html = $asset->getHtml();

            if ( $asset instanceof MinifiedAssetInterface
                 && $asset->getMinifier()->usedCache() === false
            ) {
                $message = "The {$assetKey} was updated.";
                $this->toastService->addMessage( 'info', $message );
            }

            $this->document->head->injectHtml( $html, $assetKey );
            $profiler?->stop();
        }
    }
}
