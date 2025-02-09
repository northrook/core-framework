<?php

declare(strict_types=1);

namespace Core\Framework;

use Core\Assets\AssetManager;
use Core\Framework\Service\ToastService;
use Core\View\{ComponentFactory, DocumentEngine, TemplateEngine};
use Psr\Log\LoggerInterface;

class ResponseRenderer
{
    protected ?string $content = null;

    public function __construct(
        public readonly DocumentEngine     $documentEngine,
        public readonly TemplateEngine     $templateEngine,
        public readonly ComponentFactory   $componentFactory,
        public readonly AssetManager       $assetManager,
        protected readonly ToastService    $toastService,
        protected readonly LoggerInterface $logger,
    ) {}
}
