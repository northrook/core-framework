<?php

namespace Core\Framework\Response;

enum View
{
    case DOCUMENT;
    case CONTENT;

    public function name() : string
    {
        return \strtolower( $this->name );
    }
}
