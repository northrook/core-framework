<?php

declare(strict_types=1);

namespace Core\Framework\Event;

use Core\AssetManager;
use Core\AssetManager\Interface\MinifiedAssetInterface;
use Core\Symfony\ToastService;
use Core\View\{DocumentEngine, Html\HtmlFormatter, Template\Engine, Template\Exception\CompileException};
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Core\Framework\Lifecycle\LifecycleEvent;
use InvalidArgumentException;
use Throwable;
use Core\Framework\Response\{Parameters, Template, ResponseType};

/**
 * {@see ResponseEvent}
 *
 * Uses the {@see DocumentEngine} to generate `HTML`.
 *
 * @internal
 *
 * @author Martin Nielsen <mn@northrook.com>
 *
 * @final  ✅
 */
final class ResponseViewHandler extends LifecycleEvent
{
    private readonly ?ResponseType $type;

    private readonly ?Template $template;

    public function __construct(
        private readonly DocumentEngine $view,
        private readonly Engine         $engine,
        private readonly Parameters     $parameters,
        private readonly AssetManager   $assetManager,
        private readonly ToastService   $toastService,
    ) {}

    /**
     * @param ResponseEvent $event
     *
     * @throws CompileException
     * @throws Throwable
     */
    public function __invoke( ResponseEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }

        $this->responseProperties( $event );

        $profiler = $this->profiler?->event( 'response.'.( $this->type?->name() ?? 'render' ) );

        $this->resolveContent( $event );

        $this->handleEnqueuedAssets();

        $string = $this->view->__toString();

        // $html   = new HtmlFormatter( $string );
        // $string = $html->toString( true );

        $event->getResponse()->setContent( $string );

        $profiler?->stop();
    }

    private function responseProperties( ResponseEvent $event ) : void
    {
        // @phpstan-ignore-next-line
        $this->template = $event->getRequest()->attributes->get( '_template' );
        \assert( $this->template instanceof Template || \is_null( $this->template ) );

        // @phpstan-ignore-next-line
        $this->type = $event->getRequest()->attributes->get( '_view' );
        \assert( $this->type instanceof ResponseType || \is_null( $this->type ) );
    }

    /**
     * @param ResponseEvent $event
     *
     * @return void
     * @throws CompileException
     * @throws Throwable
     */
    private function resolveContent( ResponseEvent $event ) : void
    {
        $content = (string) $event->getResponse()->getContent();

        // If contains any whitespace, we can safely assume it not a template string
        if ( \str_contains( $content, ' ' ) ) {
            $this->profiler?->event( 'response.raw' )?->stop();
            return;
        }

        if ( $this->getSetting( 'view.template.clear_cache', false ) ) {
            $profiler = $this->profiler?->event( 'clear.cache', 'View' );
            $this->engine->clearTemplateCache();
            $profiler?->stop();
        }

        $template = null;
        $profiler = null;

        if ( \str_ends_with( $content, '.latte' ) ) {
            $profiler = $this->profiler?->event( 'response.template' );
            $template = $content;
        }
        elseif ( $this->type === ResponseType::DOCUMENT && $this->template?->document ) {
            $profiler = $this->profiler?->event( 'response.document' );
            $template = $this->template->document;
        }
        elseif ( $this->type === ResponseType::CONTENT && $this->template?->content ) {
            $profiler = $this->profiler?->event( 'response.content' );
            $this->view->contentOnly();
            $template = $this->template->content;
        }

        if ( ! $template ) {
            $handler = $this::class;
            $message = "'{$handler}' expected a Template, but none was provided.";
            throw new InvalidArgumentException( $message );
        }

        $this->view->setInnerHtml(
            $this->engine->render(
                $template,
                $this->parameters->resolve(),
                true,
            ),
        );

        $profiler?->stop();
    }

    final protected function handleEnqueuedAssets() : void
    {
        foreach ( $this->view->document->assets->getEnqueuedAssets() as $assetKey ) {
            $profiler = $this->profiler?->event( $assetKey, 'Asset' );
            $asset    = $this->assetManager->getAsset( $assetKey );

            $html = $asset->getHtml();

            if ( $asset instanceof MinifiedAssetInterface
                 && $asset->getMinifier()->usedCache() === false
            ) {
                $message = "The {$assetKey} was updated.";
                $this->toastService->addMessage( 'info', $message );
            }

            $this->view->document->head->injectHtml( $html, $assetKey );
            $profiler?->stop();
        }
    }
}
