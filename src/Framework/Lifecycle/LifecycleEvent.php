<?php

declare(strict_types=1);

namespace Core\Framework\Lifecycle;

use Core\Autowire\SettingsAccessor;
use Core\Interface\{LogHandler, Loggable};
use Core\Profiler\{Interface\Profilable, StopwatchProfiler};
use Symfony\Component\Stopwatch\Stopwatch;
use BadMethodCallException;

abstract class LifecycleEvent implements Loggable, Profilable
{
    use SettingsAccessor,
        LogHandler,
        StopwatchProfiler;

    protected static bool $handleLifecycleEvent;

    final public function setProfiler(
        ?Stopwatch $stopwatch,
        ?string    $category = null,
    ) : void {
        $this->assignProfiler( $stopwatch, $category ?? 'Lifecycle' );
    }

    final protected function skipEvent() : bool
    {
        return ! ( static::$handleLifecycleEvent ?? throw new BadMethodCallException(
            __METHOD__." is only available after the 'kernel.request::256' event.",
        ) );
    }
}
