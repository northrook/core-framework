<?php

declare(strict_types=1);

namespace Core\Framework\Response;

use Stringable;

use Symfony\Component\HttpFoundation\{Response, ResponseHeaderBag};

class ViewResponse extends Response
{
    protected ?string $charset = 'UTF-8';

    /**
     * @param View                             $view
     * @param null|false|string|Stringable     $content
     * @param int                              $status
     * @param array<string, list<null|string>> $headers
     */
    public function __construct(
        public readonly View         $view,
        null|false|string|Stringable $content = '',
        int                          $status = 200,
        array                        $headers = [],
    ) {
        parent::__construct(
            content : ( (string) $content ) ?: null,
            status  : $status,
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
