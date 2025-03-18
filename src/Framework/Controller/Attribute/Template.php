<?php

declare(strict_types=1);

namespace Core\Framework\Controller\Attribute;

use Attribute;

/**
 * Set the template name to be used by the {@see ResponseViewHandler}.
 *
 * - When set on an extending {@see CoreController}, it will be used as the wrapping layout.
 * - When set on the called `method`, it will provide the content block. or as a stand-alone render for `htmx`.
 */
#[Attribute( Attribute::TARGET_CLASS | Attribute::TARGET_METHOD )]
final readonly class Template
{
    public function __construct(
        public ?string $document = null,
        public ?string $content = null,
    ) {}
}
