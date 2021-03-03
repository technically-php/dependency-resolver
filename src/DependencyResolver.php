<?php

namespace Technically\DependencyResolver;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Technically\DependencyResolver\Arguments\Argument;
use Technically\DependencyResolver\Arguments\Type;
use Technically\DependencyResolver\Exceptions\CannotAutowireDependencyArgument;
use Technically\DependencyResolver\Exceptions\ClassCannotBeInstantiated;
use Technically\DependencyResolver\Exceptions\DependencyResolutionException;

final class DependencyResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ReflectionClass[]
     */
    private static $reflections = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $className
     * @param array $bindings
     *
     * @throws ClassCannotBeInstantiated
     */
    public function resolve(string $className, array $bindings = [])
    {
        $reflection = self::reflectClass($className);

        if (! $reflection->isInstantiable()) {
            throw new ClassCannotBeInstantiated($className);
        }

        $values = [];

        if ($constructor = $reflection->getConstructor()) {
            $arguments = $this->parseArguments($constructor);
            $values = $this->resolveArguments($className, $arguments, $bindings);
        }

        return $reflection->newInstanceArgs($values);
    }

    /**
     * @param ReflectionMethod $function
     * @return Argument[]
     */
    private function parseArguments(ReflectionMethod $function): array
    {
        $arguments = [];
        foreach ($function->getParameters() as $parameter) {
            $types = $this->getParameterTypes($parameter, $function->getDeclaringClass());

            $arguments[] = new Argument(
                $parameter->getName(),
                $types,
                $parameter->isOptional(),
                $parameter->allowsNull(),
                $parameter->isOptional() ? $parameter->getDefaultValue() : null
            );
        }

        return $arguments;
    }

    /**
     * @param string $className
     * @param Argument[] $arguments
     * @param array $bindings
     * @return array
     *
     * @throws CannotAutowireDependencyArgument
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function resolveArguments(string $className, array $arguments, array $bindings = []): array
    {
        $values = [];
        foreach ($arguments as $argument) {
            $values[] = $this->resolveArgument($className, $argument, $bindings);
        }

        return $values;
    }

    /**
     * @param string $className
     * @param Argument $argument
     * @param array $bindings
     * @return mixed|null
     *
     * @throws CannotAutowireDependencyArgument
     */
    private function resolveArgument(string $className, Argument $argument, array $bindings)
    {
        if (array_key_exists($argument->getName(), $bindings)) {
            return $bindings[$argument->getName()];
        }

        foreach ($argument->getTypes() as $type) {
            $class = $type->getClassName();
            if (! empty($class) && $this->container->has($class)) {
                try {
                    return $this->container->get($class);
                } catch (ContainerExceptionInterface $exception) {
                    throw new CannotAutowireDependencyArgument($className, $argument->getName());
                }
            }
        }

        if ($argument->isOptional()) {
            return $argument->getDefaultValue();
        }

        if ($argument->isNullable()) {
            return null;
        }

        foreach ($argument->getTypes() as $type) {
            if ($class = $type->getClassName()) {
                try {
                    return $this->resolve($class);
                } catch (DependencyResolutionException $exception) {
                    // try another one
                }
            }
        }

        throw new CannotAutowireDependencyArgument($className, $argument->getName());
    }

    private static function reflectClass(string $class): ReflectionClass
    {
        if (isset(self::$reflections[$class])) {
            return self::$reflections[$class];
        }

        return self::$reflections[$class] = new ReflectionClass($class);
    }

    /**
     * @param ReflectionParameter $parameter
     * @param string|null $className
     * @return Type[]
     */
    private function getParameterTypes(ReflectionParameter $parameter, ?string $className = null): array
    {
        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType) {
            return [
                new Type($type->getName(), $className),
            ];
        }

        if (class_exists(ReflectionUnionType::class) && $type instanceof ReflectionUnionType) {
             array_map(
                function (ReflectionNamedType $type) use ($className): Type {
                    return new Type($type->getName(), $className);
                },
                $type->getTypes()
            );
        }

        return [];
    }
}
