<?php

namespace Core\Framework\Response;

enum ResponseType
{
    case DOCUMENT;
    case CONTENT;

    public function name() : string
    {
        return \strtolower( $this->name );
    }
}
