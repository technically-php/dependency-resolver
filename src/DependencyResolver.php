<?php

namespace Technically\DependencyResolver;

use LogicException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Technically\DependencyResolver\Arguments\Argument;
use Technically\DependencyResolver\Arguments\Type;
use Technically\DependencyResolver\Exceptions\ClassCannotBeInstantiated;

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
            $values = $this->resolveArguments($arguments, $bindings);
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
     * @param Argument[] $arguments
     * @param array $bindings
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function resolveArguments(array $arguments, array $bindings = []): array
    {
        $values = [];
        foreach ($arguments as $argument) {
            $values[] = $this->resolveArgument($argument, $bindings);
        }

        return $values;
    }

    /**
     * @param Argument $argument
     * @param array $bindings
     * @return mixed|null
     */
    private function resolveArgument(Argument $argument, array $bindings)
    {
        if (array_key_exists($argument->getName(), $bindings)) {
            return $bindings[$argument->getName()];
        }

        foreach ($argument->getTypes() as $type) {
            $class = $type->getClassName();
            if (! empty($class) && $this->container->has($class)) {
                return $this->container->get($class);
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
                return $this->resolve($class);
            }
        }

        throw new LogicException("Cannot resolve argument: `{$argument->getName()}`.");
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
