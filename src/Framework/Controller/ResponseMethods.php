<?php

declare(strict_types=1);

namespace Core\Framework\Controller;

use Core\Autowire\ServiceLocator;
use Core\Framework\Exception\HttpNotFoundException;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\{BinaryFileResponse,
    File,
    JsonResponse,
    RedirectResponse,
    Request,
    Response,
    ResponseHeaderBag
};
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Exception;

/**
 * @author Martin Nielsen <mn@northrook.com>
 */
trait ResponseMethods
{
    use ServiceLocator;

    /**
     * @param string              $name
     * @param array<string,mixed> $parameters
     * @param bool                $relative
     *
     * @return string
     */
    final protected function generateRoutePath(
        string $name,
        array  $parameters = [],
        bool   $relative = false,
    ) : string {
        return $this->urlGenerator()->generate(
            $name,
            $parameters,
            $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH,
        );
    }

    /**
     * @param string              $name
     * @param array<string,mixed> $parameters
     * @param bool                $relative
     *
     * @return string
     */
    final protected function generateRouteUrl(
        string $name,
        array  $parameters = [],
        bool   $relative = false,
    ) : string {
        return $this->urlGenerator()->generate(
            $name,
            $parameters,
            $relative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    /**
     * Forwards the request to another controller.
     *
     * @param string              $controller The controller name (a string like "App\Controller\PostController::index" or "App\Controller\PostController" if it is invokable)
     * @param array<string,mixed> $path
     * @param array<string,mixed> $query
     *
     * @return Response
     */
    protected function forwardToController( string $controller, array $path = [], array $query = [] ) : Response
    {
        $request             = $this->getService( Request::class );
        $path['_controller'] = $controller;
        $subRequest          = $request->duplicate( $query, null, $path );

        try {
            return $this->getService( HttpKernelInterface::class )->handle(
                $subRequest,
                HttpKernelInterface::SUB_REQUEST,
            );
        }
        catch ( Exception $exception ) {
            throw new HttpNotFoundException( previous : $exception );
        }
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @param string              $route
     * @param array<string,mixed> $parameters
     * @param int                 $status     The HTTP status code (302 "Found" by default)
     *
     * @return RedirectResponse
     */
    protected function redirectToRoute(
        string $route,
        array  $parameters = [],
        int    $status = 302,
    ) : RedirectResponse {
        // TODO : [md] Log redirects

        $url = $this->getService( RouterInterface::class )->generate( $route, $parameters );

        return new RedirectResponse( $url, $status );
    }

    /**
     * Returns a {@see JsonResponse} using the {@see SerializerInterface} if available.
     *
     * - Will use the {@see SerializerInterface} assigned to the {@see ServiceLocator} by default.
     * - Pass a custom {@see SerializerInterface} as the last argument to override the default.
     * - Pass `false` to use the {@see JsonResponse} built in `json_encode`.
     *
     * @param mixed                          $data
     * @param int                            $status
     * @param array<string,mixed>            $headers
     * @param array<string,mixed>            $context
     * @param null|false|SerializerInterface $serializer
     *
     * @return JsonResponse
     */
    protected function jsonResponse(
        mixed                          $data,
        int                            $status = Response::HTTP_OK,
        array                          $headers = [],
        array                          $context = [],
        SerializerInterface|null|false $serializer = null,
    ) : JsonResponse {
        if ( $serializer !== false ) {
            $serializer ??= $this->getService( SerializerInterface::class );
            $context = \array_merge( ['json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS], $context );
            $json    = $serializer->serialize( $data, 'json', $context );

            return new JsonResponse( $json, $status, $headers, true );
        }

        return new JsonResponse( $data, $status, $headers );
    }

    /**
     * Return {@see File} object with original or customized
     *  file name and disposition header.
     *
     * @param SplFileInfo|string $file
     * @param ?string            $fileName
     * @param string             $disposition
     *
     * @return BinaryFileResponse
     */
    protected function fileResponse(
        SplFileInfo|string $file,
        ?string            $fileName = null,
        string             $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT,
    ) : BinaryFileResponse {
        $response = new BinaryFileResponse( $file );
        $fileName ??= $response->getFile()->getFilename();

        return $response->setContentDisposition( $disposition, $fileName );
    }

    private function urlGenerator() : UrlGeneratorInterface
    {
        return $this->getService( UrlGeneratorInterface::class );
    }
}
