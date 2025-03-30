<?php

namespace Core\Framework\Response;

enum ResponseView
{
    case DOCUMENT;
    case CONTENT;

    public function name() : string
    {
        return \strtolower( $this->name );
    }
}
