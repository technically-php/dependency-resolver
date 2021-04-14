<?php

namespace Technically\DependencyResolver;

use ArgumentCountError;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Technically\CallableReflection\CallableReflection;
use Technically\CallableReflection\Parameters\ParameterReflection;
use Technically\DependencyResolver\Exceptions\CannotAutowireArgument;
use Technically\DependencyResolver\Exceptions\CannotAutowireDependencyArgument;
use Technically\DependencyResolver\Exceptions\ClassCannotBeInstantiated;
use Technically\DependencyResolver\Exceptions\DependencyResolutionException;
use Technically\NullContainer\NullContainer;

final class DependencyResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container ?: new NullContainer();
    }

    /**
     * @param string $className
     * @return mixed|object|void
     *
     * @throws InvalidArgumentException If class does not exist.
     * @throws ContainerExceptionInterface If error occurs while retrieving the existing entry from the container.
     * @throws ClassCannotBeInstantiated If class cannot be instantiated.
     * @throws CannotAutowireDependencyArgument If a dependency (of any nesting level) cannot be resolved.
     */
    public function resolve(string $className)
    {
        if (! class_exists($className) && ! interface_exists($className)) {
            throw new InvalidArgumentException("`{$className}` is not a valid class name.");
        }

        if ($this->container->has($className)) {
            return $this->container->get($className);
        }

        return $this->construct($className);
    }

    /**
     * @param string $className
     * @param array $bindings
     *
     * @throws ClassCannotBeInstantiated
     * @throws CannotAutowireDependencyArgument
     */
    public function construct(string $className, array $bindings = [])
    {
        if (! class_exists($className) && ! interface_exists($className)) {
            throw new InvalidArgumentException("`{$className}` is not a valid class name.");
        }

        try {
            $reflection = CallableReflection::fromConstructor($className);
        } catch (InvalidArgumentException $exception) {
            throw new ClassCannotBeInstantiated($className);
        }

        try {
            $values = $this->resolveParameters($reflection->getParameters(), $bindings);
        } catch (CannotAutowireArgument $exception) {
            throw new CannotAutowireDependencyArgument($className, $exception->getArgumentName(), $exception);
        }

        return $reflection->apply($values ?? []);
    }

    /**
     * @param callable $callable
     * @param array $bindings
     * @return mixed
     *
     * @throws ArgumentCountError
     * @throws CannotAutowireArgument
     */
    public function call(callable $callable, array $bindings = [])
    {
        $reflection = CallableReflection::fromCallable($callable);

        $values = $this->resolveParameters($reflection->getParameters(), $bindings);

        return $reflection->apply($values);
    }

    /**
     * @param ParameterReflection[] $parameters
     * @param array $bindings
     * @return array
     *
     * @throws CannotAutowireArgument
     */
    private function resolveParameters(array $parameters, array $bindings = []): array
    {
        $values = [];
        foreach ($parameters as $i => $parameter) {
            if (array_key_exists($i, $bindings)) {
                $values[] = $bindings[$i];
                continue;
            }

            if (array_key_exists($parameter->getName(), $bindings)) {
                $values[] = $bindings[$parameter->getName()];
                continue;
            }

            $values[] = $this->resolveParameter($parameter);
        }

        return $values;
    }

    /**
     * @param ParameterReflection $parameter
     * @return mixed|null
     *
     * @throws CannotAutowireArgument
     */
    private function resolveParameter(ParameterReflection $parameter)
    {
        foreach ($parameter->getTypes() as $type) {
            if ($type->isClassRequirement() && $this->container->has($class = $type->getClassRequirement())) {
                try {
                    return $this->container->get($class);
                } catch (ContainerExceptionInterface $exception) {
                    throw new CannotAutowireArgument($parameter->getName());
                }
            }
        }

        if ($parameter->isOptional()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->isNullable() && count($parameter->getTypes()) > 0) {
            return null;
        }

        foreach ($parameter->getTypes() as $type) {
            if ($type->isClassRequirement()) {
                $class = $type->getClassRequirement();
                try {
                    return $this->construct($class);
                } catch (DependencyResolutionException $exception) {
                    // try another one
                }
            }
        }

        throw new CannotAutowireArgument($parameter->getName());
    }
}
