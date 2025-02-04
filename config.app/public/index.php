<?php

/** @noinspection ALL */

declare(strict_types=1);

require __DIR__.'/../vendor/autoload_runtime.php';

return static fn( array $_ ) => new \App\Kernel( $_['APP_ENV'], $_['APP_DEBUG'] );
