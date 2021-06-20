<?php declare(strict_types=1);


namespace Limbo\Http\UriParser\UriParts;

use Limbo\Http\Exception\InvalidUriPartException;

/**
 * URI part "scheme"
 *
 * Class UriScheme
 * @package Limbo\Http\UriParser\UriParts
 * @link https://tools.ietf.org/html/rfc3986#section-3.1
 */
class UriScheme implements UriPartInterface
{
    /**
     * The scheme value
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
            throw new InvalidUriPartException('URI part "scheme" must be a string');
        }

        $regex = '/^(?:[A-Za-z][0-9A-Za-z\+\-\.]*)?$/';
        if (!preg_match($regex, $value)) {
            throw new InvalidUriPartException('Invalid URI part "scheme"');
        }

        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): string
    {
        return strtolower($this->value);
    }
}
