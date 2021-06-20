<?php declare(strict_types=1);


namespace Limbo\Http\UriParser\UriParts;

/**
 * URI part "userinfo"
 *
 * Class UriUserInfo
 * @package Limbo\Http\UriParser\UriParts
 * @link https://tools.ietf.org/html/rfc3986#section-3.2.1
 */
class UriUserInfo implements UriPartInterface
{
    /**
     * The userinfo value
     *
     * @var string
     */
    protected string $value = '';

    /**
     * @inheritDoc
     */
    public function __construct($user, $pass = null)
    {
        $user = new UriUser($user);
        $this->value = $user->getValue();

        if (null !== $pass) {
            $pass = new UriPass($pass);
            $this->value .= ':' . $pass->getValue();
        }
    }

    /**
     * @inheritDoc
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
