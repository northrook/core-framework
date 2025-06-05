<?php

declare(strict_types=1);

namespace Core\Framework\Event;

use Core\AssetManager;
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

        $profile = 'response.'.( $this->type?->name() ?? 'render' );

        $this->profilerStart( $profile );

        $this->resolveContent( $event );

        $this->handleEnqueuedAssets();

        $string = $this->view->__toString();

        // $html   = new HtmlFormatter( $string );
        // $string = $html->toString( true );

        $event->getResponse()->setContent( $string );

        $this->profilerStop( $profile );
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
     * @throws Throwable
     */
    private function resolveContent( ResponseEvent $event ) : void
    {
        $content = (string) $event->getResponse()->getContent();

        // If contains any whitespace, we can safely assume it not a template string
        if ( \str_contains( $content, ' ' ) ) {
            return;
        }

        if ( $this->getSetting(
            'view.template.clear_cache',
            true, // :: DEBUG
        ) ) {
            $this->engine->clearTemplateCache();
        }

        $this->profilerStart( 'response.content' );

        $template = null;
        $profiler = null;

        if ( \str_ends_with( $content, '.latte' ) ) {
            $template = $content;
        }
        elseif ( $this->type === ResponseType::DOCUMENT && $this->template?->document ) {
            $template = $this->template->document;
        }
        elseif ( $this->type === ResponseType::CONTENT && $this->template?->content ) {
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

        $this->profilerStop( 'response.content' );
    }

    final protected function handleEnqueuedAssets() : void
    {
        $this->profilerStart( 'response.assets' );

        foreach ( $this->view->document->assets->getEnqueuedAssets() as $assetKey ) {
            $asset = $this->assetManager->getAsset( $assetKey );

            try {
                $asset = $this->assetManager->getAsset( $assetKey );
            }
            catch ( Throwable $exception ) {
                dump( $exception );

                continue;
            }
            // $html = $asset->getHtml();
            //
            // if ( $asset instanceof MinifiedAssetInterface
            //      && $asset->getMinifier()->usedCache() === false
            // ) {
            //     $message = "The {$assetKey} was updated.";
            //     $this->toastService->addMessage( 'info', $message );
            // }
            //
            // $this->view->document->head->injectHtml( $html, $assetKey );

            // $this->profilerLap( 'response.assets' );
        }
        $this->profilerStop( 'response.assets' );
    }
}
