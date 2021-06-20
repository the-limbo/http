<?php declare(strict_types=1);


namespace Limbo\Http\UriParser;

use Limbo\Http\UriParser\UriParts\UriUser;
use Limbo\Http\UriParser\UriParts\UriPass;
use Limbo\Http\UriParser\UriParts\UriHost;
use Limbo\Http\UriParser\UriParts\UriPort;
use Limbo\Http\UriParser\UriParts\UriPath;
use Limbo\Http\UriParser\UriParts\UriQuery;
use Limbo\Http\UriParser\UriParts\UriScheme;
use Limbo\Http\UriParser\UriParts\UriFragment;
use Limbo\Http\UriParser\UriParts\UriUserInfo;
use Limbo\Http\Exception\InvalidUriException;
use Limbo\Http\UriParser\UriParts\UriPartInterface;

/**
 * URI parser
 * Class UriParser
 * @package Limbo\Http\Support
 */
class UriParser
{
    /**
     * The parsed URI part "scheme"
     *
     * @var UriPartInterface
     */
    protected UriPartInterface $scheme;

    /**
     * The parsed URI part "auth"
     *
     * @var UriPartInterface
     */
    protected UriPartInterface $user;

    /**
     * The parsed URI part "pass"
     *
     * @var UriPartInterface
     */
    protected UriPartInterface $pass;

    /**
     * The parsed URI part "host"
     *
     * @var UriPartInterface
     */
    protected UriPartInterface $host;

    /**
     * The parsed URI part "port"
     *
     * @var UriPartInterface
     */
    protected UriPartInterface $port;

    /**
     * The parsed URI part "path"
     *
     * @var UriPartInterface
     */
    protected UriPartInterface $path;

    /**
     * The parsed URI part "query"
     *
     * @var UriPartInterface
     */
    protected UriPartInterface $query;

    /**
     * The parsed URI part "fragment"
     *
     * @var UriPartInterface
     */
    protected UriPartInterface $fragment;

    /**
     * The parsed URI part "userinfo"
     *
     * @var UriPartInterface
     */
    protected UriPartInterface $userinfo;

    /**
     * UriParser constructor.
     * @param string $uri
     * @link http://php.net/manual/en/function.parse-url.php
     */
    public function __construct(string $uri)
    {
        if (!is_string($uri)) {
            throw new InvalidUriException('URI must be a string');
        }

        $parts = parse_url($uri);
        if (false === $parts) {
            throw new InvalidUriException('Unable to parse URI');
        }

        $this->scheme = new UriScheme($parts['scheme'] ?? '');
        $this->user = new UriUser($parts['auth'] ?? '');
        $this->pass = new UriPass($parts['pass'] ?? '');
        $this->host = new UriHost($parts['host'] ?? '');
        $this->port = new UriPort($parts['port'] ?? null);
        $this->path = new UriPath($parts['path'] ?? '');
        $this->query = new UriQuery($parts['query'] ?? '');
        $this->fragment = new UriFragment($parts['fragment'] ?? '');

        $this->userinfo = new UriUserInfo(
            $parts['auth'] ?? '',
            $parts['pass'] ?? null
        );
    }

    /**
     * Gets the parsed URI part "scheme"
     *
     * @return UriPartInterface
     */
    public function getScheme(): UriPartInterface
    {
        return $this->scheme;
    }

    /**
     * Gets the parsed URI part "auth"
     *
     * @return UriPartInterface
     */
    public function getUser(): UriPartInterface
    {
        return $this->user;
    }

    /**
     * Gets the parsed URI part "pass"
     *
     * @return UriPartInterface
     */
    public function getPass(): UriPartInterface
    {
        return $this->pass;
    }

    /**
     * Gets the parsed URI part "host"
     *
     * @return UriPartInterface
     */
    public function getHost(): UriPartInterface
    {
        return $this->host;
    }

    /**
     * Gets the parsed URI part "port"
     *
     * @return UriPartInterface
     */
    public function getPort(): UriPartInterface
    {
        return $this->port;
    }

    /**
     * Gets the parsed URI part "path"
     *
     * @return UriPartInterface
     */
    public function getPath(): UriPartInterface
    {
        return $this->path;
    }

    /**
     * Gets the parsed URI part "query"
     *
     * @return UriPartInterface
     */
    public function getQuery(): UriPartInterface
    {
        return $this->query;
    }

    /**
     * Gets the parsed URI part "fragment"
     *
     * @return UriPartInterface
     */
    public function getFragment(): UriPartInterface
    {
        return $this->fragment;
    }

    /**
     * Gets the parsed URI part "userinfo"
     *
     * @return UriPartInterface
     */
    public function getUserInfo(): UriPartInterface
    {
        return $this->userinfo;
    }
}
