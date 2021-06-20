<?php declare(strict_types=1);


namespace Limbo\Http\Message;

use Throwable;
use RuntimeException;
use Psr\Http\Message\StreamInterface;
use Limbo\Http\Exception\UntellableStreamException;
use Limbo\Http\Exception\UnseekableStreamException;
use Limbo\Http\Exception\UnwritableStreamException;
use Limbo\Http\Exception\UnreadableStreamException;

/**
 * Stream
 * @package Limbo\Http\Message
 *
 * @link https://www.php-fig.org/psr/psr-7/
 */
class Stream implements StreamInterface
{
    /**
     * Resource of the stream
     *
     * @var resource|null
     */
    protected $resource;

    /**
     * @var resource|string
     */
    protected $stream;

    /**
     * Stream constructor.
     * @param resource|string $resource
     * @param string $mode
     */
    public function __construct($resource, string $mode = 'r')
    {
        $this->setStream($resource, $mode);
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        try {
            if ($this->isReadable()) {
                if ($this->isSeekable()) {
                    $this->rewind();
                }

                return $this->getContents();
            }
        } catch (Throwable $e) {
            // ignore...
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        if (!is_resource($this->resource)) {
            return;
        }

        $resource = $this->detach();
        fclose($resource);
    }

    /**
     * @inheritDoc
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;

        return $resource;
    }

    /**
     * @inheritDoc
     */
    public function getSize()
    {
        if (!is_resource($this->resource)) {
            return null;
        }

        $stats = fstat($this->resource);
        if (false === $stats) {
            return null;
        }

        return $stats['size'];
    }

    /**
     * @inheritDoc
     */
    public function tell(): int
    {
        if (!is_resource($this->resource)) {
            throw new UntellableStreamException('Stream is not resourceable.');
        }

        $result = ftell($this->resource);
        if (false === $result) {
            throw new UntellableStreamException('Unable to get the stream pointer position.');
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function eof(): bool
    {
        if (!is_resource($this->resource)) {
            return true;
        }

        return feof($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function isSeekable()
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $metadata = stream_get_meta_data($this->resource);

        return $metadata['seekable'];
    }

    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!is_resource($this->resource)) {
            throw new UnseekableStreamException('Stream is not resourceable.');
        }

        if (!$this->isSeekable()) {
            throw new UnseekableStreamException('Stream is not seekable.');
        }

        $result = fseek($this->resource, $offset, $whence);

        if (!(0 === $result)) {
            throw new UnseekableStreamException('Unable to move the stream pointer to the given position.');
        }
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        if (!is_resource($this->resource)) {
            throw new UnseekableStreamException('Stream is not resourceable.');
        }

        if (!$this->isSeekable()) {
            throw new UnseekableStreamException('Stream is not seekable.');
        }

        $result = fseek($this->resource, 0, SEEK_SET);

        if (!(0 === $result)) {
            throw new UnseekableStreamException('Unable to move the stream pointer to beginning.');
        }
    }

    /**
     * @inheritDoc
     */
    public function isWritable(): bool
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $metadata = stream_get_meta_data($this->resource);

        return !(false === strpbrk($metadata['mode'], '+acwx'));
    }

    /**
     * @inheritDoc
     */
    public function write($string): int
    {
        if (!is_resource($this->resource)) {
            throw new UnwritableStreamException('Stream is not resourceable.');
        }

        if (!$this->isWritable()) {
            throw new UnwritableStreamException('Stream is not writable.');
        }

        $result = fwrite($this->resource, $string);

        if (false === $result) {
            throw new UnwritableStreamException('Unable to write to the stream.');
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function isReadable(): bool
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $metadata = stream_get_meta_data($this->resource);

        return !(false === strpbrk($metadata['mode'], '+r'));
    }

    /**
     * @inheritDoc
     */
    public function read($length): string
    {
        if (!is_resource($this->resource)) {
            throw new UnreadableStreamException('Stream is not resourceable.');
        }

        if (!$this->isReadable()) {
            throw new UnreadableStreamException('Stream is not readable.');
        }

        $result = fread($this->resource, $length);
        if (false === $result) {
            throw new UnreadableStreamException('Unable to read from the stream.');
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getContents(): string
    {
        if (!is_resource($this->resource)) {
            throw new UnreadableStreamException('Stream is not resourceable.');
        }

        if (!$this->isReadable()) {
            throw new UnreadableStreamException('Stream is not readable.');
        }

        $result = stream_get_contents($this->resource);
        if (false === $result) {
            throw new UnreadableStreamException('Unable to read remainder of the stream.');
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata($key = null)
    {
        if (!is_resource($this->resource)) {
            return null;
        }

        $metadata = stream_get_meta_data($this->resource);
        if (!(null === $key)) {
            return $metadata[$key] ?? null;
        }

        return $metadata;
    }

    /**
     * Set stream
     * @param resource|string $stream
     * @param string $mode
     */
    private function setStream($stream, string $mode = 'r'): void
    {
        $error = null;
        $resource = $stream;

        if (is_string($stream)) {
            set_error_handler(function ($e) use (&$error) {
                if ($e !== E_WARNING) {
                    return;
                }

                $error = $e;
            });
            $resource = fopen($stream, $mode);
            restore_error_handler();
        }

        if ($error) {
            throw new RuntimeException('Invalid stream reference provided.');
        }

        if ($stream !== $resource) {
            $this->stream = $stream;
        }

        $this->resource = $resource;
    }
}
