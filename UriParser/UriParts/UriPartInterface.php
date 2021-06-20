<?php declare(strict_types=1);


namespace Limbo\Http\UriParser\UriParts;

use Limbo\Http\Exception\InvalidUriPartException;

/**
 * Interface UriPartInterface
 * @package Limbo\Http\UriParser\UriParts
 */
interface UriPartInterface
{
    /**
     * UriPartInterface constructor.
     *
     * @param mixed $value
     * @throws InvalidUriPartException
     */
    public function __construct($value);

    /**
     * @return mixed
     */
    public function getValue();
}
