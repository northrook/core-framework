<?php

namespace Core\Framework;

use \Symfony\Component\Routing\Attribute as Symfony;
use Attribute;

#[Attribute( Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD )]
final class Route extends Symfony\Route
{
    public const string ASSETS = 'assets.';
}
