# Getting started with Limbo/Http
[![Latest Stable Version](http://poser.pugx.org/limbo/http/v)](https://packagist.org/packages/limbo/http)
[![Latest Unstable Version](http://poser.pugx.org/limbo/http/v/unstable)](https://packagist.org/packages/limbo/http)
[![License](http://poser.pugx.org/limbo/http/license)](https://packagist.org/packages/limbo/http)
[![Total Downloads](http://poser.pugx.org/limbo/http/downloads)](https://packagist.org/packages/limbo/http)

[psr/http-message](https://www.php-fig.org/psr/psr-7/) implementation

[psr/http-factory](https://www.php-fig.org/psr/psr-17/) implementation

## Features

 * Factories
 * Extended functionality for http-message classes.

## Install via [composer](https://getcomposer.org/)

```
composer require limbo/http
```

### Create ServerRequest

```php
require_once 'vendor/autoload.php';

use Limbo\Http\Factory\ServerRequestFactory;

$request = ServerRequestFactory::fromGlobals();
```

or 

```php
require_once 'vendor/autoload.php';

use Limbo\Http\Factory\ResponseFactory;
use Limbo\Http\Factory\ServerRequestFactory;

$request = ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);
```
