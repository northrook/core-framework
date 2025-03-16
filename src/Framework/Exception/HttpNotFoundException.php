<?php

declare(strict_types=1);

namespace Core\Framework\Exception;

use Throwable;

class HttpNotFoundException extends HttpException
{
    /**
     * @param string                             $message
     * @param null|string                        $description
     * @param null|Throwable                     $previous
     * @param array<string,null|string|string[]> $headers
     */
    public function __construct(
        string            $message = 'Not Found',
        protected ?string $description = null,
        ?Throwable        $previous = null,
        array             $headers = [],
    ) {
        parent::__construct(
            self::NOT_FOUND,
            $message,
            $previous,
            $headers,
        );
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function getStatusCode() : int
    {
        return self::NOT_FOUND;
    }
}
