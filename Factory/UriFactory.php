<?php declare(strict_types=1);


namespace Limbo\Http\Factory;

use Limbo\Http\Message\Uri;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * Uri Factory
 * Class UriFactory
 * @package Limbo\Http\Factory
 * @link https://www.php-fig.org/psr/psr-17/
 */
class UriFactory implements UriFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
