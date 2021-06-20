<?php declare(strict_types=1);


namespace Limbo\Http\Factory;

use Limbo\Http\Message\Request;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * Request Factory
 * Class RequestFactory
 * @package Limbo\Http\Factory
 * @link https://www.php-fig.org/psr/psr-17/
 */
class RequestFactory implements RequestFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        if (!($uri instanceof UriInterface)) {
            $uri = (new UriFactory)->createUri($uri);
        }

        $body = (new StreamFactory)->createStream();

        return (new Request)
            ->withMethod($method)
            ->withUri($uri)
            ->withBody($body);
    }
}
