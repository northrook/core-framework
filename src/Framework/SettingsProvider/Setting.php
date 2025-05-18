<?php

declare(strict_types=1);

namespace Core\Framework\SettingsProvider;

use _Dev\Attribute\TODO;
use Core\Interface\DataInterface;
use JetBrains\PhpStorm\Immutable;

#[TODO]
final class Setting implements DataInterface
{
    /** @var string `setting.dot.notated.key` */
    #[Immutable]
    public readonly string $key;

    /** @var string Setting soft-name */
    public readonly string $name;

    /** @var null|array<array-key, scalar>|scalar */
    public readonly null|array|bool|float|int|string $value;

    /** @var array<int,null|array<array-key, scalar>|scalar> */
    protected array $versions = [];

    public function __construct(
        string $key,
        string $name,
    ) {
        $this->key  = $key;
        $this->name = $name;
    }
}
