<?php

declare(strict_types=1);

namespace Core\Framework;

use Core\Assets\AssetManager;
use Core\Framework\Controller\Attribute\Template;
use Core\Framework\Service\ToastService;
use Core\View\{ComponentFactory, Document, DocumentEngine, TemplateEngine};
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ResponseRenderer
{
    protected ?string $content = null;

    public bool $clearTemplateCache = true;

    public function __construct(
        public readonly DocumentEngine     $documentEngine,
        public readonly TemplateEngine     $templateEngine,
        public readonly ComponentFactory   $componentFactory,
        public readonly AssetManager       $assetManager,
        protected readonly Document        $document,
        protected readonly ToastService    $toastService,
        protected readonly LoggerInterface $logger,
    ) {}

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

        if ( ! $template ) {
            $this->logger->error(
                '{method} expected a Template, but none was provided.',
                ['method' => __METHOD__, 'event' => $event],
            );
            return $this;
        }

        $contentOnly = $event->getRequest()->headers->has( 'hx-request' );

        $view = $contentOnly ? $template->content : $template->document;

        if ( ! $view ) {
            $this->logger->error(
                '{method} expected a Template, an object was provided, but no templates were set.',
                [
                    'method'   => __METHOD__,
                    'event'    => $event,
                    'template' => $template,
                ],
            );
            return $this;
        }

        $this->content = $this->templateEngine->render( $view );

        $this->documentEngine->setInnerHtml( $this->content );

        return $this;
    }

    final public function getResponse() : Response
    {
        $this->handleEnqueuedAssets();
        $content = (string) $this->documentEngine;
        return new Response(
            $content,
        );
    }

    final protected function handleToastMessages() : void
    {
        if ( ! $this->toastService->hasMessages() ) {
            return;
        }

        $toasts = [];

        foreach ( $this->toastService->getAllMessages() as $message ) {
            $toasts[] = $message->message;
            // $toasts[] = $this->componentFactory->render(
            //     'view.component.toast',
            //     $message->getArguments(),
            // );
        }

        $this->document->body->content( $toasts, true );
    }

    final protected function handleEnqueuedAssets() : void
    {
        foreach ( $this->document->assets->getEnqueuedAssets() as $assetKey ) {
            $assetModel = $this->assetManager->getAssetHtml( $assetKey );
            if ( ! $assetModel ) {
                $this->logger->warning( \sprintf( 'Asset %s not found', $assetKey ) );
            }
            else {
                $this->document->head->injectHtml( $assetModel, $assetKey );
            }
        }
    }
}
