<?php declare(strict_types=1);


namespace Limbo\Http\Message;

use RuntimeException;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * HTTP Response Message
 * @package Limbo\Http\Message
 *
 * @link https://tools.ietf.org/html/rfc7230
 * @link https://www.php-fig.org/psr/psr-7/
 */
class Response extends Message implements ResponseInterface
{
    /**
     * Status code of the message
     *
     * @var int
     */
    protected int $statusCode = 200;

    /**
     * Reason phrase of the message
     *
     * @var string
     */
    protected string $reasonPhrase = 'OK';

    /**
     * List of Phrases
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     */
    protected const PHRASES = [
        // 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        // 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        // 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        // 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * Response constructor.
     * @param string|resource|StreamInterface $body
     * @param int $code
     * @param array $headers
     */
    public function __construct($body = 'php://memory', int $code = 200, array $headers = [])
    {
        $this->statusCode = $code;
        $this->headers = $headers;
        $this->setStream($body, 'wb+');
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @inheritDoc
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $code = $this->filterStatus($code);
        $reasonPhrase = $this->filterReasonPhrase($reasonPhrase);

        $clone = clone $this;
        $clone->statusCode = $code;
        $clone->reasonPhrase = $reasonPhrase;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getReasonPhrase(): string
    {
        if ($this->reasonPhrase !== '') {
            return $this->reasonPhrase;
        }

        if (isset(self::PHRASES[$this->statusCode])) {
            return self::PHRASES[$this->statusCode];
        }

        return '';
    }

    /**
     * Filter HTTP status code.
     * @param  int $status HTTP status code.
     * @return int
     * @throws InvalidArgumentException If an invalid HTTP status code is provided.
     */
    protected function filterStatus(int $status): int
    {
        if (!is_integer($status) || $status < 100 || $status > 599) {
            throw new InvalidArgumentException('Invalid HTTP status code.');
        }

        return $status;
    }

    /**
     * Filter Reason Phrase
     * @param mixed $reasonPhrase
     * @throws InvalidArgumentException
     */
    protected function filterReasonPhrase($reasonPhrase = ''): string
    {
        if (is_object($reasonPhrase) && method_exists($reasonPhrase, '__toString')) {
            $reasonPhrase = (string) $reasonPhrase;
        }

        if (!is_string($reasonPhrase)) {
            throw new InvalidArgumentException('Response reason phrase must be a string.');
        }

        if (strpos($reasonPhrase, "\r") || strpos($reasonPhrase, "\n")) {
            throw new InvalidArgumentException(
                'Reason phrase contains one of the following prohibited characters: \r \n'
            );
        }

        return $reasonPhrase;
    }

    /**
     * Write data to the response body.
     * @internal It's not PSR-7 method.
     * Proxies to the underlying stream and writes the provided data to it.
     * @param string $data
     * @return static
     */
    public function write(string $data): ResponseInterface
    {
        $this->getBody()->write($data);

        return $this;
    }

    /**
     * Redirect to specified location
     * @internal It's not PSR-7 method.
     * @param string    $url The redirect destination.
     * @param int|null  $status The redirect HTTP status code.
     * @return ResponseInterface|Response
     */
    public function withRedirect(string $url, ?int $status = null)
    {
        $response = $this->withHeader('Location', $url);

        if ($status === null) {
            $status = 302;
        }

        return $response->withStatus($status);
    }

    /**
     * Write JSON to Response Body.
     * @internal It's not PSR-7 method.
     * @param  mixed     $data   The data
     * @param  int|null  $status The HTTP status code
     * @param  int       $options Json encoding options
     * @param  int       $depth Json encoding max depth
     * @return ResponseInterface|Response
     */
    public function withJson($data, ?int $status = null, int $options = 0, int $depth = 512)
    {
        json_encode(null);
        $json = (string)json_encode($data, $options, $depth);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(json_last_error_msg(), json_last_error());
        }

        $body = $this->getBody();
        $body->write($json);
        $response = $this
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withBody($body);

        if ($status !== null) {
            $response = $response->withStatus($status);
        }

        return $response;
    }

    /**
     * Convert response to string.
     * @internal It's not PSR-7 method.
     * @return string
     */
    public function __toString(): string
    {
        $output = sprintf(
            'HTTP/%s %s %s%s',
            $this->getProtocolVersion(),
            $this->getStatusCode(),
            $this->getReasonPhrase(),
            "\r\n"
        );

        foreach ($this->getHeaders() as $name => $values) {
            $output .= sprintf('%s: %s', $name, $this->getHeaderLine($name)) . "\r\n";
        }

        $output .= "\r\n";
        $output .= (string)$this->getBody();

        return $output;
    }
}
