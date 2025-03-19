<?php

declare(strict_types=1);

namespace Core\Framework\Response;

use Attribute;

/**
 * Set the template name to be used by the {@see ResponseViewHandler}.
 *
 * - When set on an extending {@see CoreController}, it will be used as the wrapping layout.
 * - When set on the called `method`, it will provide the content block. or as a stand-alone render for `htmx`.
 */
#[Attribute( Attribute::TARGET_CLASS | Attribute::TARGET_METHOD )]
final class Template
{
    public function __construct(
        public ?string $content = null,
        public ?string $document = null,
    ) {}
}
