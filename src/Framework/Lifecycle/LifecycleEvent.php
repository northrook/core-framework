<?php

namespace Core\Framework\Lifecycle;

use Core\Symfony\DependencyInjection\SettingsAccessor;
use Core\Profiler\{Interface\Profilable, StopwatchProfiler};
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait};
use Symfony\Component\Stopwatch\Stopwatch;
use BadMethodCallException;

abstract class LifecycleEvent implements Profilable, LoggerAwareInterface
{
    use SettingsAccessor,
        StopwatchProfiler,
        LoggerAwareTrait;

    protected const string CATEGORY = 'Lifecycle';

    protected static bool $handleLifecycleEvent;

    final public function setProfiler(
        ?Stopwatch $stopwatch,
        ?string    $category = self::CATEGORY,
    ) : void {
        $this->assignProfiler( $stopwatch, $category );
    }

    final protected function skipEvent() : bool
    {
        return ! ( static::$handleLifecycleEvent ?? throw new BadMethodCallException(
            __METHOD__." is only available after the 'kernel.request::256' event.",
        ) );
    }
}
