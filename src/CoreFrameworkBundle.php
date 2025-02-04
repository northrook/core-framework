<?php

declare( strict_types = 1 );

namespace Core;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Core Symfony Framework.
 *
 * @author Martin Nielsen
 */
final class CoreFrameworkBundle extends AbstractBundle
{
    public function boot() : void
    {
        parent::boot();
        dump( 'Hello there!' );
    }
}
