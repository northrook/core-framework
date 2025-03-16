<?php

declare(strict_types=1);

namespace Core\Framework\Exception;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use RuntimeException;
use Throwable;

/**
 * Handle HTTP error exceptions.
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
abstract class HttpException extends RuntimeException implements HttpExceptionInterface
{
    public const int
        BAD_REQUEST                   = 400,
        UNAUTHORIZED                  = 401,
        FORBIDDEN                     = 403,
        NOT_FOUND                     = 404,
        NOT_ACCEPTABLE                = 406,
        TOO_MANY_REQUESTS             = 429,
        UNAVAILABLE_FOR_LEGAL_REASONS = 451,
        INTERNAL_SERVER_ERROR         = 500,
        NOT_IMPLEMENTED               = 501,
        BAD_GATEWAY                   = 502,
        SERVICE_UNAVAILABLE           = 503,
        GATEWAY_TIMEOUT               = 504;

    /**
     * @param self::*                            $code
     * @param string                             $message
     * @param null|Throwable                     $previous
     * @param array<string,null|string|string[]> $headers
     *                                                     /
     */
    public function __construct(
        int             $code,
        string          $message,
        ?Throwable      $previous = null,
        protected array $headers = [],
    ) {
        parent::__construct( $message, $code, $previous );
    }

    /**
     * @return array<string,null|string|string[]>
     */
    final public function getHeaders() : array
    {
        return $this->headers;
    }
}
