<?php

namespace Technically\DependencyResolver\Contracts;

use ArgumentCountError;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Technically\DependencyResolver\Exceptions\CannotAutowireArgument;
use Technically\DependencyResolver\Exceptions\CannotAutowireDependencyArgument;
use Technically\DependencyResolver\Exceptions\ClassCannotBeInstantiated;

interface DependencyResolver
{
    /**
     * Resolve the given class instance from the container.
     *
     * It can be either found in the container or constructed on the fly,
     * recursively auto-resolving the required parameters.
     *
     * @param class-string $className
     * @return mixed
     *
     * @throws InvalidArgumentException If the class does not exist.
     * @throws ContainerExceptionInterface If an error occurs while retrieving the existing entry from the container.
     * @throws ClassCannotBeInstantiated If the class cannot be instantiated.
     * @throws CannotAutowireDependencyArgument If a dependency (of any nesting level) cannot be resolved.
     */
    public function resolve(string $className): mixed;

    /**
     * Force-construct the given class instance using container state for auto-wiring dependencies.
     *
     * Even if the container already has the instance bound,
     * it will still be instantiated.
     *
     * @param class-string        $className
     * @param array<string,mixed> $bindings
     * @return mixed
     *
     * @throws ClassCannotBeInstantiated
     * @throws CannotAutowireDependencyArgument
     */
    public function construct(string $className, array $bindings = []): mixed;

    /**
     * Call the given callable with its arguments automatically wired using the container state.
     *
     * @template T
     * @param callable():T        $callable
     * @param array<string,mixed> $bindings
     * @return T
     *
     * @throws ArgumentCountError
     * @throws CannotAutowireArgument
     */
    public function call(callable $callable, array $bindings = []): mixed;
}