<?php

namespace Technically\DependencyResolver\Tests\Fixtures;

use Technically\DependencyResolver\DependencyResolver;

final class MyInvokableService
{
    public function __invoke(DependencyResolver $resolver, string $message, ?int $else = null): array
    {
        return [$resolver, $message, $else];
    }
}
