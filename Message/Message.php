<?php declare(strict_types=1);


namespace Limbo\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\MessageInterface;

/**
 * Hypertext Transfer Protocol Message
 * @package Limbo\Http\Message
 *
 * @link https://tools.ietf.org/html/rfc7230
 * @link https://www.php-fig.org/psr/psr-7/
 */
class Message implements MessageInterface
{
    /**
     * Protocol version for the message
     *
     * @var string
     */
    protected string $protocolVersion = '1.1';

    /**
     * Headers of the message
     *
     * @var array
     */
    protected array $headers = [];

    /**
     * Body of the message
     *
     * @var null|StreamInterface
     */
    protected ?StreamInterface $stream;

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version)
    {
        $this->validateProtocolVersion($version);
        $clone = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name): bool
    {
        $name = $this->normalizeHeaderName($name);

        return !empty($this->headers[$name]);
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name): array
    {
        $name = $this->normalizeHeaderName($name);
        if (empty($this->headers[$name])) {
            return [];
        }

        return $this->headers[$name];
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name): string
    {
        $name = $this->normalizeHeaderName($name);
        if (empty($this->headers[$name])) {
            return '';
        }

        return implode(',', $this->headers[$name]);
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value)
    {
        $this->validateHeaderName($name);
        $this->validateHeaderValue($value);

        $name = $this->normalizeHeaderName($name);
        $value = $this->normalizeHeaderValue($value);

        if (isset($this->headers[$name])) {
            $value = array_merge($this->headers[$name], $value);
        }

        $clone = clone $this;
        $clone->headers[$name] = $value;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value)
    {
        return $this->withHeader($name, $value);
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name)
    {
        $name = $this->normalizeHeaderName($name);
        $clone = clone $this;
        unset($clone->headers[$name]);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): ?StreamInterface
    {
        return $this->stream;
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body)
    {
        $clone = clone $this;
        $clone->stream = $body;

        return $clone;
    }

    /**
     * Validates the given protocol version
     *
     * @param mixed $version
     *
     * @return void
     *
     * @throws InvalidArgumentException
     *
     * @link https://tools.ietf.org/html/rfc7230#section-2.6
     * @link https://tools.ietf.org/html/rfc7540
     */
    protected function validateProtocolVersion($version): void
    {
        if (empty($version)) {
            throw new InvalidArgumentException(
                'HTTP protocol version can not be empty'
            );
        }
        if (!is_string($version)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP protocol version; must be a string, received %s',
                (is_object($version) ? get_class($version) : gettype($version))
            ));
        }

        // HTTP/1 uses a "<major>.<minor>" numbering scheme to indicate
        // versions of the protocol, while HTTP/2 does not.
        if (!preg_match('#^(1\.[01]|2)$#', $version)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP protocol version "%s" provided',
                $version
            ));
        }
    }

    /**
     * Validates the given header name
     *
     * @param mixed $headerName
     *
     * @return void
     *
     * @throws InvalidArgumentException
     *
     * @link https://tools.ietf.org/html/rfc7230#section-3.2
     */
    protected function validateHeaderName($headerName): void
    {
        if (!is_string($headerName)) {
            throw new InvalidArgumentException('Header name must be a string');
        }

        if (preg_match("@^[!#$%&'*+.^_`|~0-9A-Za-z-]+$@", $headerName) !== 1) {
            throw new InvalidArgumentException('Header name must be an RFC 7230 compatible string.');
        }
    }

    /**
     * Validates the given header value
     *
     * @param mixed $headerValue
     *
     * @return void
     *
     * @throws InvalidArgumentException
     *
     * @link https://tools.ietf.org/html/rfc7230#section-3.2
     */
    protected function validateHeaderValue($headerValue): void
    {
        $items = is_array($headerValue) ? $headerValue : [$headerValue];

        if (empty($items)) {
            throw new InvalidArgumentException(
                'Header values must be a string or an array of strings, empty array given.'
            );
        }

        $pattern = "@^[ \t\x21-\x7E\x80-\xFF]*$@";
        foreach ($items as $item) {
            $hasInvalidType = !is_numeric($item) && !is_string($item);
            $rejected = $hasInvalidType || preg_match($pattern, (string) $item) !== 1;
            if ($rejected) {
                throw new InvalidArgumentException(
                    'Header values must be RFC 7230 compatible strings.'
                );
            }
        }
    }

    /**
     * Normalizes the given header name
     *
     * @param string $headerName
     *
     * @return string
     *
     * @link https://tools.ietf.org/html/rfc7230#section-3.2
     */
    protected function normalizeHeaderName(string $headerName): string
    {
        // Each header field consists of a case-insensitive field name...
        return strtolower($headerName);
    }

    /**
     * Normalizes the given header value
     *
     * @param string|array $headerValue
     *
     * @return array
     */
    protected function normalizeHeaderValue($headerValue): array
    {
        $headerValue = (array) $headerValue;
        return array_values($headerValue);
    }

    /**
     * Set stream
     * @internal It's not PSR-7 method.
     * @param $stream
     * @param string $mode
     * @void
     */
    protected function setStream($stream, string $mode): void
    {
        if ($stream instanceof StreamInterface) {
            $this->stream = $stream;
        }

        if (!is_string($stream) && !is_resource($stream)) {
            throw new InvalidArgumentException(
                'Stream must be a string stream resource identifier, '
                . 'an actual stream resource, '
                . 'or a Psr\Http\Message\StreamInterface implementation'
            );
        }

        $this->stream = new Stream($stream, $mode);
    }
}
