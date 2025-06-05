<?php

declare(strict_types=1);

namespace Core\Framework\Lifecycle;

use Core\Autowire\{Logger, Profiler, SettingsProvider};
use BadMethodCallException;

abstract class LifecycleEvent
{
    use SettingsProvider,
        Logger,
        Profiler;

    protected static bool $handleLifecycleEvent;

    final protected function skipEvent() : bool
    {
        return ! ( static::$handleLifecycleEvent ?? throw new BadMethodCallException(
            __METHOD__." is only available after the 'kernel.request::256' event.",
        ) );
    }
}
