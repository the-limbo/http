<?php declare(strict_types=1);


namespace Limbo\Http\UriParser\UriParts;

use Limbo\Http\Exception\InvalidUriPartException;

/**
 * Uri part "port"
 *
 * Class UriPort
 * @package Limbo\Http\UriParser\UriParts
 * @link https://tools.ietf.org/html/rfc3986#section-3.2.3
 */
class UriPort implements UriPartInterface
{
    /**
     * The port value
     *
     * @var int|null
     */
    protected ?int $value = null;

    /**
     * @inheritDoc
     */
    public function __construct($value)
    {
        $min = 1;
        $max = 2 ** 16;

        if (null === $value) {
            return;
        }

        if (!is_int($value)) {
            throw new InvalidUriPartException('URI part "port" must be an integer');
        }

        if (!($value >= $min && $value <= $max)) {
            throw new InvalidUriPartException('Invalid URI part "port"');
        }

        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): ?int
    {
        return $this->value;
    }
}
