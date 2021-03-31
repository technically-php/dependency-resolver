![Status][badge]

# Technically Dependency Resolver

`Technically\DependencyResolver` is a simple yet powerful tool to instantiate classes
autowiring their dependencies by resolving them from a [PSR-11][1] container 
or recursively instantiating them with *DependencyResolver* itself.

## Features

- Based on PSR-11
- PHP 8.0 ready (supports union type hints; see examples below)
- PHP 7.1+ compatible
- Recursive dependencies autowiring
- Semver
- Tests

## Installation

Use [Composer][2] package manager to add *Technically\DependencyResolver* to your project:

```
composer require technically/dependency-resolver
```

## Example

```php
<?php

final class MyFancyService 
{
    public function __construct(callable|LoggerInterface $log) 
    {
        // initialize
    }
}

// Resolve service instance, providing dependencies in-place:
$resolver = new DependencyResolver();
$service = $resolver->resolve(MyFancyService::class, [
    'log' => function (string $priority, string $message) {
        error_log("[$priority]: $message");
    }]
);

// Resolve service instance, resolving dependencies from container:
$container->set(LoggerInterface::class, $logger);
$resolver = new DependencyResolver($container);
$service = $resolver->resolve(MyFancyService::class);


```

## Changelog

All notable changes to this project will be documented in the [CHANGELOG](./CHANGELOG.md) file.


## Credits

- Implemented by [Ivan Voskoboinyk][3]
- Heavily inspired by Dawid Kraczkowski's work in [igniphp/container][4]

[1]: https://www.php-fig.org/psr/psr-11/
[2]: https://getcomposer.org/
[3]: https://github.com/e1himself?utm_source=web&utm_medium=github&utm_campaign=technically/dependency-resolver
[4]: https://github.com/igniphp/container
[badge]: https://github.com/technically-php/dependency-resolver/actions/workflows/test.yml/badge.svg
