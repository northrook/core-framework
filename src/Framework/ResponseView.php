<?php

namespace Core\Framework;

use Core\Assets\AssetManager;
use Core\Framework\Service\ToastService;
use Core\View\{ComponentFactory, DocumentView, TemplateEngine};
use Psr\Log\LoggerInterface;

class ResponseView
{
    private ?string $content = null;

    public function __construct(
        public readonly DocumentView       $view,
        public readonly TemplateEngine     $templateEngine,
        public readonly ComponentFactory   $componentFactory,
        public readonly AssetManager       $assetManager,
        protected readonly ToastService    $toastService,
        protected readonly LoggerInterface $logger,
    ) {}
}
