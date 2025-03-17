<?php

declare(strict_types=1);

namespace Core\Framework\Response;

use Core\Interface\ActionInterface;
use Symfony\Component\HttpFoundation\{HeaderBag, RequestStack, ResponseHeaderBag};

// : Content Type
//  https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Type
//  https://stackoverflow.com/a/48704300/14986455

// : Robots
//  https://www.madx.digital/glossary/x-robots-tag
//  https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag

final class Headers implements ActionInterface
{
    protected ?HeaderBag $tempRequestHeaderBag = null;

    protected readonly ResponseHeaderBag $responseHeaderBag;

    public function __construct( private readonly RequestStack $requestStack ) {}

    /**
     * Set one or more response headers.
     *
     * - Assigned to the {@see ResponseHeaderBag::class}.
     *
     * @param array<string, null|array<string, string>|bool|string>|string $set
     * @param null|bool|string|string[]                                    $value
     * @param bool                                                         $replace [true]
     *
     * @return Headers
     */
    public function __invoke(
        string|array           $set,
        bool|string|array|null $value = null,
        bool                   $replace = true,
    ) : Headers {
        // Set multiple values
        if ( \is_array( $set ) ) {
            foreach ( $set as $key => $value ) {
                $this->__invoke( $key, $value, $replace );
            }

            return $this;
        }

        if ( \is_bool( $value ) ) {
            $value = $value ? 'true' : 'false';
        }

        $this->response()->set( $set, $value, $replace );

        return $this;
    }

    public function response() : ResponseHeaderBag
    {
        return $this->responseHeaderBag ??= new ResponseHeaderBag();
    }

    /**
     * Access the {@see HeaderBag}.
     *
     * @return HeaderBag
     */
    public function request() : HeaderBag
    {
        $currentHeaderBag = $this->requestStack->getCurrentRequest()?->headers;

        if ( ! $currentHeaderBag ) {
            return $this->tempRequestHeaderBag ??= new HeaderBag();
        }

        if ( isset( $this->tempRequestHeaderBag ) ) {
            $currentHeaderBag->add( $this->tempRequestHeaderBag->all() );
            unset( $this->tempRequestHeaderBag );
        }

        return $currentHeaderBag;
    }
}
