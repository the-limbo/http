<?php declare(strict_types=1);


namespace Limbo\Http\Factory;

use Limbo\Http\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * Response Factory
 * Class ResponseFactory
 * @package Limbo\Http\Factory
 * @link https://www.php-fig.org/psr/psr-17/
 */
class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return (new Response())->withStatus($code, $reasonPhrase);
    }
}
