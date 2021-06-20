<?php declare(strict_types=1);


namespace Limbo\Http\Factory;

use Limbo\Http\Message\Request;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;

/**
 * Server Request Factory
 * Class ServerRequestFactory
 * @package Limbo\Http\Factory
 * @link https://www.php-fig.org/psr/psr-17/
 */
class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * Creates the server request instance from superglobals variables
     * @param array|null $server
     * @param array|null $query
     * @param array|null $body
     * @param array|null $cookies
     * @param array|null $files
     * @return Request|ServerRequestInterface
     * @internal It's not PSR-7 method.
     * @link http://php.net/manual/en/language.variables.superglobals.php
     * @link https://www.php-fig.org/psr/psr-15/meta/
     */
    public static function fromGlobals(
        array $server = null,
        array $query = null,
        array $body = null,
        array $cookies = null,
        array $files = null
    ) {
        $server  = $server  ?? $_SERVER ?? [];
        $query   = $query   ?? $_GET    ?? [];
        $body    = $body    ?? $_POST   ?? [];
        $cookies = $cookies ?? $_COOKIE ?? [];
        $files   = $files   ?? $_FILES  ?? [];

        $request = (new Request)
            ->withProtocolVersion(request_http_version($server))
            ->withBody(request_body())
            ->withMethod(request_method($server))
            ->withUri(request_uri($server))
            ->withServerParams($server)
            ->withCookieParams($cookies)
            ->withQueryParams($query)
            ->withUploadedFiles(request_files($files))
            ->withParsedBody($body);

        foreach (request_headers($server) as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }

    /**
     * @inheritDoc
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        if (!($uri instanceof UriInterface)) {
            $uri = (new UriFactory)->createUri($uri);
        }

        $body = (new StreamFactory)->createStream();

        return (new Request)
            ->withMethod($method)
            ->withUri($uri)
            ->withServerParams($serverParams)
            ->withBody($body);
    }
}
