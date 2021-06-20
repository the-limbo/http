<?php declare(strict_types=1);


namespace Limbo\Http\UriParser\UriParts;

use Limbo\Http\Exception\InvalidUriPartException;

/**
 * URI part "query"
 *
 * Class UriQuery
 * @package Limbo\Http\UriParser\UriParts
 * @link https://tools.ietf.org/html/rfc3986#section-3.4
 */
class UriQuery implements UriPartInterface
{
    /**
     * The query value
     *
     * @var string
     */
    protected string $value = '';

    /**
     * @inheritDoc
     */
    public function __construct($value)
    {
        if ('' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new InvalidUriPartException('URI part "query" must be a string');
        }

        $regex = '/(?:(?:%[0-9A-Fa-f]{2}|[0-9A-Za-z\-\._~\!\$&\'\(\)\*\+,;\=\:@\/\?]+)|(.?))/u';

        $this->value = (string)preg_replace_callback($regex, function ($match) {
            return isset($match[1]) ? rawurlencode($match[1]) : $match[0];
        }, $value);
    }

    /**
     * @inheritDoc
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
