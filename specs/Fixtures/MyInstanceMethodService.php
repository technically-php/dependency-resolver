<?php

namespace Technically\DependencyResolver\Specs\Fixtures;

use Technically\DependencyResolver\DependencyResolver;

final class MyInstanceMethodService
{
    public function test(DependencyResolver $resolver, string $message, int $else = null): array
    {
        return [$resolver, $message, $else];
    }
}
