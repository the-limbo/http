<?php declare(strict_types=1);


namespace Limbo\Http\Factory;

use Limbo\Http\Message\Stream;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Limbo\Http\Exception\UnopenableStreamException;

/**
 * Stream Factory
 * Class StreamFactory
 * @package Limbo\Http\Factory
 * @link https://www.php-fig.org/psr/psr-17/
 */
class StreamFactory implements StreamFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'r+b');

        fwrite($resource, $content);
        rewind($resource);

        return new Stream($resource);
    }

    /**
     * @inheritDoc
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        // See http://php.net/manual/en/function.fopen.php
        $resource = @fopen($filename, $mode);

        if (false === $resource) {
            throw new UnopenableStreamException(
                sprintf('Unable to open file "%s" in mode "%s"', $filename, $mode)
            );
        }

        return new Stream($resource);
    }

    /**
     * @inheritDoc
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}
