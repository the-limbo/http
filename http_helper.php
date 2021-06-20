<?php declare(strict_types=1);

use Limbo\Http\Factory\UriFactory;
use Psr\Http\Message\UriInterface;
use Limbo\Http\Factory\StreamFactory;
use Psr\Http\Message\StreamInterface;
use Limbo\Http\Factory\UploadedFileFactory;

/**
 * Gets the request body
 *
 * MUST NOT be used outside of this package.
 *
 * @return StreamInterface
 *
 * @link http://php.net/manual/en/wrappers.php.php
 */
function request_body(): StreamInterface
{
    $resource = fopen('php://temp', 'r+b');

    stream_copy_to_stream(fopen('php://input', 'rb'), $resource);

    rewind($resource);

    return (new StreamFactory)->createStreamFromResource($resource);
}

/**
 * Normalizes the given uploaded files
 *
 * MUST NOT be used outside of this package.
 *
 * @param array $files
 *
 * @return array
 *
 * @link http://php.net/manual/en/reserved.variables.files.php
 */
function request_files(array $files): array
{
    $walker = function ($path, $size, $error, $name, $type) use (&$walker) {
        if (!is_array($path)) {
            $stream = (new StreamFactory)->createStreamFromFile($path, 'rb');

            return (new UploadedFileFactory)->createUploadedFile($stream, $size, $error, $name, $type);
        }

        $result = [];
        foreach ($path as $key => $value) {
            $result[$key] = $walker($value, $size[$key], $error[$key], $name[$key], $type[$key]);
        }

        return $result;
    };

    $result = [];
    foreach ($files as $key => $file) {
        if (UPLOAD_ERR_NO_FILE === $file['error']) {
            continue;
        }

        $result[$key] = $walker($file['tmp_name'], $file['size'], $file['error'], $file['name'], $file['type']);
    }

    return $result;
}

/**
 * Gets the request headers from the given server environment
 *
 * MUST NOT be used outside of this package.
 *
 * @param array $server
 *
 * @return array
 *
 * @link http://php.net/manual/en/reserved.variables.server.php
 */
function request_headers(array $server): array
{
    $result = [];
    foreach ($server as $key => $value) {
        if (!(0 === strncmp('HTTP_', $key, 5))) {
            continue;
        }

        $name = substr($key, 5);
        $name = strtolower($name);
        $name = strtr($name, '_', ' ');
        $name = ucwords($name);
        $name = strtr($name, ' ', '-');

        $result[$name] = $value;
    }

    return $result;
}

/**
 * Gets the request HTTP version from the given server environment
 *
 * MUST NOT be used outside of this package.
 *
 * @param array $server
 *
 * @return string
 *
 * @link http://php.net/manual/en/reserved.variables.server.php
 */
function request_http_version(array $server): string
{
    $regex = '/^HTTP\/(\d(?:\.\d)?)$/';

    if (isset($server['SERVER_PROTOCOL'])) {
        if (preg_match($regex, $server['SERVER_PROTOCOL'], $matches)) {
            return $matches[1];
        }
    }

    return '1.1';
}

/**
 * Gets the request method from the given server environment
 *
 * MUST NOT be used outside of this package.
 *
 * @param array $server
 *
 * @return string
 *
 * @link http://php.net/manual/en/reserved.variables.server.php
 */
function request_method(array $server): string
{
    return $server['REQUEST_METHOD'] ?? 'GET';
}

/**
 * Gets the request URI from the given server environment
 *
 * MUST NOT be used outside of this package.
 *
 * @param array $server
 *
 * @return UriInterface
 *
 * @link http://php.net/manual/en/reserved.variables.server.php
 */
function request_uri(array $server) : UriInterface
{
    if (array_key_exists('HTTPS', $server)) {
        if (!('off' === $server['HTTPS'])) {
            $scheme = 'https://';
        }
    }

    if (array_key_exists('HTTP_HOST', $server)) {
        $domain = $server['HTTP_HOST'];
    } elseif (array_key_exists('SERVER_NAME', $server)) {
        $domain = $server['SERVER_NAME'];
        if (array_key_exists('SERVER_PORT', $server)) {
            $domain .= ':' . $server['SERVER_PORT'];
        }
    }

    if (array_key_exists('REQUEST_URI', $server)) {
        $target = $server['REQUEST_URI'];
    } elseif (array_key_exists('PHP_SELF', $server)) {
        $target = $server['PHP_SELF'];
        if (array_key_exists('QUERY_STRING', $server)) {
            $target .= '?' . $server['QUERY_STRING'];
        }
    }

    return (new UriFactory)->createUri(
        ($scheme ?? 'http://') .
        ($domain ?? 'localhost') .
        ($target ?? '/')
    );
}
