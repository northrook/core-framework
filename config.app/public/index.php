<?php

/** @noinspection ALL */

declare(strict_types=1);

require __DIR__.'/../vendor/autoload_runtime.php';

return static fn( array $context ) => new \App\Kernel( (string) $context['APP_ENV'], (bool) $context['APP_DEBUG'] );
