<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use function Support\{is_path, normalize_path};

final class FinalizeParametersPass extends CompilerPass
{
    public function compile( ContainerBuilder $container ) : void
    {
        foreach ( $this->parameterBag->all() as $key => $value ) {
            if ( is_path( $value ) ) {
                $this->parameterBag->set( $key, normalize_path( $value ) );
            }

            if ( \str_starts_with( $key, 'fragment.' ) ) {
                $this->parameterBag->remove( $key );
            }
        }
    }
}
