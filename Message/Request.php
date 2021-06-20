<?php declare(strict_types=1);


namespace Limbo\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * HTTP Request Message (include ServerRequest)
 *
 * Class Request
 * @package Limbo\Http\Message
 */
class Request extends Message implements ServerRequestInterface
{
    /**
     * Method of the message
     *
     * @var string
     */
    protected string $method = 'GET';

    /**
     * URI of the message
     *
     * @var null|UriInterface
     */
    protected ?UriInterface $uri;

    /**
     * Request target of the message
     *
     * @var null|string
     */
    protected ?string $requestTarget;

    /**
     * The request query parameters
     *
     * @var array
     */
    protected array $queryParams = [];

    /**
     * The request cookie parameters
     *
     * @var array
     */
    protected array $cookieParams = [];

    /**
     * The server parameters
     *
     * @var array
     */
    protected array $serverParams = [];

    /**
     * The request attributes
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * The request parsed body
     *
     * @var mixed
     */
    protected $parsedBody;

    /**
     * The request uploaded files
     *
     * @var UploadedFileInterface[]
     */
    protected array $uploadedFiles = [];

    /**
     * @inheritDoc
     */
    public function getRequestTarget(): ?string
    {
        if (!(null === $this->requestTarget)) {
            return $this->requestTarget;
        }

        if (!($this->uri instanceof UriInterface)) {
            return '/';
        }

        // https://tools.ietf.org/html/rfc7230#section-5.3.1
        // https://tools.ietf.org/html/rfc7230#section-2.7
        //
        // origin-form = absolute-path [ "?" query ]
        // absolute-path = 1*( "/" segment )
        if (!(0 === strncmp($this->uri->getPath(), '/', 1))) {
            return '/';
        }

        $origin = $this->uri->getPath();
        if (!('' === $this->uri->getQuery())) {
            $origin .= '?' . $this->uri->getQuery();
        }

        return $origin;
    }

    /**
     * @inheritDoc
     */
    public function withRequestTarget($requestTarget)
    {
        $this->validateRequestTarget($requestTarget);

        $clone = clone $this;
        $clone->requestTarget = $requestTarget;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function withMethod($method)
    {
        $this->validateMethod($method);

        $clone = clone $this;
        $clone->method = strtoupper($method);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getUri(): ?UriInterface
    {
        return $this->uri;
    }

    /**
     * @inheritDoc
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clone = clone $this;
        $clone->uri = $uri;

        if ('' === $uri->getHost() || ($preserveHost && $clone->hasHeader('host'))) {
            return $clone;
        }

        $host = $uri->getHost();
        if (!(null === $uri->getPort())) {
            $host .= ':' . $uri->getPort();
        }

        return $clone->withHeader('host', $host);
    }

    /**
     * @inheritDoc
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * @inheritDoc
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * @inheritDoc
     */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @inheritDoc
     */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->queryParams = $query;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * @inheritDoc
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        // Validates the given uploaded files structure
        array_walk_recursive($uploadedFiles, function ($uploadedFile) {
            if (!($uploadedFile instanceof UploadedFileInterface)) {
                throw new InvalidArgumentException('Invalid uploaded files structure');
            }
        });

        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @inheritDoc
     */
    public function withParsedBody($data)
    {
        $clone = clone $this;
        $clone->parsedBody = $data;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    public function getAttribute($name, $default = null)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * @inheritDoc
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withoutAttribute($name)
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);

        return $clone;
    }

    /**
     * Gets a new instance of the message with the given server parameters
     * @internal It's not PSR-7 method.
     * @param array $serverParams
     * @return Request|RequestInterface|ServerRequestInterface
     */
    public function withServerParams(array $serverParams)
    {
        $clone = clone $this;
        $clone->serverParams = $serverParams;

        return $clone;
    }

    /**
     * Fetch cookie value from cookies sent by the client to the server.
     * @internal It's not PSR-7 method.
     * @param string $key     The attribute name.
     * @param mixed  $default Default value to return if the attribute does not exist.
     *
     * @return mixed
     */
    public function getCookieParam(string $key, $default = null)
    {
        $cookies = $this->getCookieParams();
        $result = $default;

        if (isset($cookies[$key])) {
            $result = $cookies[$key];
        }

        return $result;
    }

    /**
     * Fetch parameter value from body or query string (in that order).
     * If key set null fetch associative array of body and query string parameters.
     * @internal It's not PSR-7 method.
     * @param  string|null $key The parameter key.
     * @param  mixed  $default The default value.
     * @return mixed The parameter value.
     */
    public function get(?string $key = null, $default = null)
    {
        if ($key === null) {
            $params = $this->getQueryParams();
            $postParams = $this->getParsedBody();

            if ($postParams) {
                $params = array_merge($params, (array)$postParams);
            }

            return $params;
        }

        $postParams = $this->getParsedBody();
        $getParams = $this->getQueryParams();
        $result = $default;

        if (is_array($postParams) && isset($postParams[$key])) {
            $result = $postParams[$key];
        } elseif (is_object($postParams) && property_exists($postParams, $key)) {
            $result = $postParams->$key;
        } elseif (isset($getParams[$key])) {
            $result = $getParams[$key];
        }

        return $result;
    }

    /**
     * Does this use a given method?
     * @internal It's not PSR-7 method.
     * @param  string $method HTTP method
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        return $this->getMethod() === $method;
    }

    /**
     * Is this a DELETE serverRequest?
     * @internal It's not PSR-7 method.
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->isMethod('DELETE');
    }

    /**
     * Is this a GET serverRequest?
     * @internal It's not PSR-7 method.
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    /**
     * Is this a HEAD serverRequest?
     * @internal It's not PSR-7 method.
     * @return bool
     */
    public function isHead(): bool
    {
        return $this->isMethod('HEAD');
    }

    /**
     * Is this a OPTIONS serverRequest?
     * @internal It's not PSR-7 method.
     * @return bool
     */
    public function isOptions(): bool
    {
        return $this->isMethod('OPTIONS');
    }

    /**
     * Is this a PATCH serverRequest?
     * @internal It's not PSR-7 method.
     * @return bool
     */
    public function isPatch(): bool
    {
        return $this->isMethod('PATCH');
    }

    /**
     * Is this a POST serverRequest?
     * @internal It's not PSR-7 method.
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    /**
     * Is this a PUT serverRequest?
     * @internal It's not PSR-7 method.
     * @return bool
     */
    public function isPut(): bool
    {
        return $this->isMethod('PUT');
    }

    /**
     * Is this an XHR serverRequest?
     * @internal It's not PSR-7 method.
     * @return bool
     */
    public function isXhr(): bool
    {
        return $this->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Validates the given method
     * @param mixed $method
     * @return void
     * @throws InvalidArgumentException
     *
     * @link https://tools.ietf.org/html/rfc7230#section-3.1.1
     */
    protected function validateMethod($method): void
    {
        if (!is_string($method)) {
            throw new InvalidArgumentException('Unsupported HTTP method; must be a string, received %s');
        }

        if (!preg_match("/^[!#$%&'*+.^_`|~0-9a-z-]+$/i", $method)) {
            throw new InvalidArgumentException(sprintf('Unsupported HTTP method "%s" provided', $method));
        }
    }

    /**
     * Validates the given request-target
     * @param mixed $requestTarget
     * @return void
     * @throws InvalidArgumentException
     *
     * @link https://tools.ietf.org/html/rfc7230#section-5.3
     */
    protected function validateRequestTarget($requestTarget): void
    {
        if (!is_string($requestTarget)) {
            throw new InvalidArgumentException('HTTP request-target must be a string');
        }

        if (!preg_match('/^[\x21-\x7E\x80-\xFF]+$/', $requestTarget)) {
            throw new InvalidArgumentException(sprintf('The given request-target "%s" is not valid', $requestTarget));
        }
    }
}
