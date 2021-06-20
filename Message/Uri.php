<?php declare(strict_types=1);


namespace Limbo\Http\Message;

use Psr\Http\Message\UriInterface;
use Limbo\Http\UriParser\UriParser;
use Limbo\Http\UriParser\UriParts\UriHost;
use Limbo\Http\UriParser\UriParts\UriPort;
use Limbo\Http\UriParser\UriParts\UriPath;
use Limbo\Http\UriParser\UriParts\UriQuery;
use Limbo\Http\UriParser\UriParts\UriScheme;
use Limbo\Http\UriParser\UriParts\UriUserInfo;
use Limbo\Http\UriParser\UriParts\UriFragment;

/**
 * Uniform Resource Identifier
 * Class Uri
 * @package Limbo\Http\Message
 * @link https://tools.ietf.org/html/rfc3986
 * @link https://www.php-fig.org/psr/psr-7/
 */
class Uri implements UriInterface
{
    /**
     * The URI component "scheme"
     *
     * @var string
     */
    protected string $scheme = '';

    /**
     * The URI component "userinfo"
     *
     * @var string
     */
    protected string $userinfo = '';

    /**
     * The URI component "host"
     *
     * @var string
     */
    protected string $host = '';

    /**
     * The URI component "port"
     *
     * @var int|null
     */
    protected ?int $port;

    /**
     * The URI component "path"
     *
     * @var string
     */
    protected string $path = '';

    /**
     * The URI component "query"
     *
     * @var string
     */
    protected string $query = '';

    /**
     * The URI component "fragment"
     *
     * @var string
     */
    protected string $fragment = '';

    /**
     * Uri constructor.
     */
    public function __construct($uri = '')
    {
        if ('' === $uri) {
            return;
        }

        $parts = new UriParser($uri);

        $this->scheme = $parts->getScheme()->getValue();
        $this->userinfo = $parts->getUserInfo()->getValue();
        $this->host = $parts->getHost()->getValue();
        $this->port = $parts->getPort()->getValue();
        $this->path = $parts->getPath()->getValue();
        $this->query = $parts->getQuery()->getValue();
        $this->fragment = $parts->getFragment()->getValue();
    }

    /**
     * @inheritDoc
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @inheritDoc
     */
    public function getAuthority()
    {
        $authority = $this->getHost();

        // Host is the basic subcomponent.
        if ('' === $authority) {
            return '';
        }

        $userinfo = $this->getUserInfo();
        if (! ('' === $userinfo)) {
            $authority = $userinfo . '@' . $authority;
        }

        $port = $this->getPort();
        if (! (null === $port)) {
            $authority = $authority . ':' . $port;
        }

        return $authority;
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo()
    {
        return $this->userinfo;
    }

    /**
     * @inheritDoc
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @inheritDoc
     */
    public function getPort()
    {
        $scheme = $this->getScheme();

        // The 80 is the default port number for the HTTP protocol.
        if (80 === $this->port && 'http' === $scheme) {
            return null;
        }

        // The 443 is the default port number for the HTTPS protocol.
        if (443 === $this->port && 'https' === $scheme) {
            return null;
        }

        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @inheritDoc
     */
    public function withScheme($scheme)
    {
        $clone = clone $this;
        $component = new UriScheme($scheme);
        $clone->scheme = $component->getValue();

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo($user, $password = null)
    {
        $clone = clone $this;
        $component = new UriUserInfo($user, $password);
        $clone->userinfo = $component->getValue();

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withHost($host)
    {
        $clone = clone $this;
        $component = new UriHost($host);
        $clone->host = $component->getValue();

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withPort($port)
    {
        $clone = clone $this;
        $component = new UriPort($port);
        $clone->port = $component->getValue();

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withPath($path)
    {
        $clone = clone $this;
        $component = new UriPath($path);
        $clone->path = $component->getValue();

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withQuery($query)
    {
        $clone = clone $this;
        $component = new UriQuery($query);
        $clone->query = $component->getValue();

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withFragment($fragment)
    {
        $clone = clone $this;
        $component = new UriFragment($fragment);
        $clone->fragment = $component->getValue();

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        $uri = '';

        $scheme = $this->getScheme();
        if (! ('' === $scheme)) {
            $uri .= $scheme . ':';
        }

        $authority = $this->getAuthority();
        if (! ('' === $authority)) {
            $uri .= '//' . $authority;
        }

        $path = $this->getPath();
        if (! ('' === $path)) {
            $uri .= $path;
        }

        $query = $this->getQuery();
        if (! ('' === $query)) {
            $uri .= '?' . $query;
        }

        $fragment = $this->getFragment();
        if (! ('' === $fragment)) {
            $uri .= '#' . $fragment;
        }

        return $uri;
    }

    /**
     * Return the fully qualified base URL.
     * Note that this method never includes a trailing slash
     * [!] It's not PSR-7 method.
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();

        return ($scheme !== '' ? $scheme . ':' : '') . ($authority !== '' ? '//' . $authority : '');
    }
}
