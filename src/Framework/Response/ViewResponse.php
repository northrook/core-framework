<?php

declare(strict_types=1);

namespace Core\Framework\Response;

use Stringable;

use Symfony\Component\HttpFoundation\{Response, ResponseHeaderBag};
use const Support\AUTO;

class ViewResponse extends Response
{
    protected ?string $charset = 'UTF-8';

    /**
     * @param ResponseType                     $view
     * @param null|false|string|Stringable     $content
     * @param null|int                         $status
     * @param array<string, list<null|string>> $headers
     */
    public function __construct(
        public readonly ResponseType $view,
        null|false|string|Stringable $content = '',
        ?int                         $status = AUTO,
        array                        $headers = [],
    ) {
        parent::__construct(
            content : ( (string) $content ) ?: null,
            status  : $status ?? Response::HTTP_OK,
            headers : $headers,
        );
    }

    /**
     * @param array<string, list<null|string>>|Headers|ResponseHeaderBag $headers
     *
     * @return void
     */
    final public function setHeaders( Headers|ResponseHeaderBag|array $headers ) : void
    {
        $headers = match ( true ) {
            $headers instanceof Headers           => $headers->response()->allPreserveCase(),
            $headers instanceof ResponseHeaderBag => $headers->allPreserveCase(),
            default                               => $headers,
        };

        $headers = \array_merge_recursive(
            $this->headers->allPreserveCase(),
            $headers,
        );

        $this->headers = new ResponseHeaderBag( $headers );
    }
}
