<?php declare(strict_types=1);


namespace Limbo\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * Emit response body
 * Class Emitter
 * @package Limbo\Http
 */
class Emitter
{
    /**
     * @var int
     */
    protected int $responseSize;

    /**
     * Emitter constructor.
     * @param int $responseSize
     */
    public function __construct(int $responseSize = 4096)
    {
        $this->responseSize = $responseSize;
    }

    /**
     * Factory to create Emitter
     * @return Emitter
     */
    public static function create(): Emitter
    {
        return new static();
    }

    /**
     * Send the response to client
     * @param ResponseInterface $response
     */
    public function emit(ResponseInterface $response): void
    {
        $empty = static::isEmptyResponse($response);
        if (headers_sent() === false) {
            $this->emitStatusLine($response);
            $this->emitHeaders($response);
        }

        if (!$empty) {
            $this->emitBody($response);
        }
    }

    /**
     * Emit response headers
     * @param ResponseInterface $response
     */
    private function emitHeaders(ResponseInterface $response): void
    {
        foreach ($response->getHeaders() as $name => $values) {
            $first = strtolower($name) !== 'set-cookie';
            foreach ($values as $value) {
                $header = sprintf('%s: %s', $name, $value);
                header($header, $first);
                $first = false;
            }
        }
    }

    /**
     * Emit status line
     * @param ResponseInterface $response
     */
    private function emitStatusLine(ResponseInterface $response): void
    {
        $statusLine = sprintf(
            'HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );
        header($statusLine, true, $response->getStatusCode());
    }

    /**
     * Emit response body
     * @param ResponseInterface $response
     */
    private function emitBody(ResponseInterface $response): void
    {
        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        $amountToRead = (int)$response->getHeaderLine('Content-Length');
        if (!$amountToRead) {
            $amountToRead = $body->getSize();
        }

        if ($amountToRead) {
            while ($amountToRead > 0 && !$body->eof()) {
                $length = min($this->responseSize, $amountToRead);
                $data = $body->read($length);
                echo $data;

                $amountToRead -= strlen($data);

                if (connection_status() !== CONNECTION_NORMAL) {
                    break;
                }
            }
        } else {
            while (!$body->eof()) {
                echo $body->read($this->responseSize);
                if (connection_status() !== CONNECTION_NORMAL) {
                    break;
                }
            }
        }
    }

    /**
     * Is response body empty or status code is 204, 205 or 304
     * @param ResponseInterface $response
     * @return bool
     */
    public static function isEmptyResponse(ResponseInterface $response): bool
    {
        if (in_array($response->getStatusCode(), [204, 205, 304], true)) {
            return true;
        }

        $stream = $response->getBody();
        $seekable = $stream->isSeekable();
        if ($seekable) {
            $stream->rewind();
        }

        return $seekable ? $stream->read(1) === '' : $stream->eof();
    }
}
