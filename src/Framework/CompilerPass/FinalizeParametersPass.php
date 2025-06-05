<?php

declare(strict_types=1);

namespace Core\Framework\CompilerPass;

use Core\Container\CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use function Support\{is_path, normalize_path};

final class FinalizeParametersPass extends CompilerPass
{
    public function compile( ContainerBuilder $container ) : void
    {
        $parameters = $this->parameterBag->all();

        $this->parameterBag->clear();

        \ksort( $parameters );

        foreach ( $parameters as $key => $value ) {
            if ( \str_starts_with( $key, 'fragment.' ) ) {
                continue;
            }
            if ( is_path( $value ) ) {
                $value = normalize_path( $value );
            }
            $this->parameterBag->set( $key, $value );
        }
    }
}
