<?php

namespace Technically\DependencyResolver;

use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;
use Technically\DependencyResolver\Arguments\Argument;
use Technically\DependencyResolver\Arguments\Type;
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

    /**
     * @var ReflectionClass[]
     */
    private static $reflections = [];

    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container ?: new NullContainer();
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
        $reflection = self::reflectClass($className);

        if (! $reflection->isInstantiable()) {
            throw new ClassCannotBeInstantiated($className);
        }

        $values = [];

        if ($constructor = $reflection->getConstructor()) {
            $arguments = $this->parseArguments($constructor);

            try {
                $values = $this->resolveArguments($arguments, $bindings);
            } catch (CannotAutowireArgument $exception) {
                throw new CannotAutowireDependencyArgument($className, $exception->getArgumentName(), $exception);
            }
        }

        return $reflection->newInstanceArgs($values);
    }

    public function call(callable $callback, array $bindings = [])
    {
        $reflection = self::reflectCallable($callback);

        $arguments = $this->parseArguments($reflection);
        $values = $this->resolveArguments($arguments, $bindings);

        return ($callback)(...$values);
    }

    /**
     * @param ReflectionFunctionAbstract $function
     * @return Argument[]
     */
    private function parseArguments(ReflectionFunctionAbstract $function): array
    {
        $arguments = [];
        foreach ($function->getParameters() as $parameter) {
            $className = $function instanceof ReflectionMethod ? $function->getDeclaringClass()->getName() : null;
            $types = $this->getParameterTypes($parameter, $className);

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
     *
     * @throws CannotAutowireArgument
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
     *
     * @throws CannotAutowireArgument
     */
    private function resolveArgument(Argument $argument, array $bindings)
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
                    throw new CannotAutowireArgument($argument->getName());
                }
            }
        }

        if ($argument->isOptional()) {
            return $argument->getDefaultValue();
        }

        if ($argument->isNullable() && count($argument->getTypes()) > 0) {
            return null;
        }

        foreach ($argument->getTypes() as $type) {
            if ($class = $type->getClassName()) {
                try {
                    return $this->construct($class);
                } catch (DependencyResolutionException $exception) {
                    // try another one
                }
            }
        }

        throw new CannotAutowireArgument($argument->getName());
    }

    private static function reflectClass(string $class): ReflectionClass
    {
        if (isset(self::$reflections[$class])) {
            return self::$reflections[$class];
        }

        return self::$reflections[$class] = new ReflectionClass($class);
    }

    private static function reflectCallable(callable $callable): ReflectionFunctionAbstract
    {
        try {
            if ($callable instanceof Closure) {
                return new ReflectionFunction($callable);
            }

            if (is_string($callable) && function_exists($callable)) {
                return new ReflectionFunction($callable);
            }

            if (is_string($callable) && str_contains($callable, '::')) {
                return new ReflectionMethod($callable);
            }

            if (is_object($callable) && method_exists($callable, '__invoke')) {
                return new ReflectionMethod($callable, '__invoke');
            }

            if (is_array($callable)) {
                return new ReflectionMethod($callable[0], $callable[1]);
            }
        } catch (ReflectionException $exception) {
            throw new RuntimeException(
                sprintf('Failed reflecting the given callable: %s.', get_debug_type($callable)),
                0,
                $exception
            );
        }

        throw new InvalidArgumentException(
            sprintf("Cannot reflect the given callable: %s.", get_debug_type($callable))
        );
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

        /** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
        if (class_exists(\ReflectionUnionType::class) && $type instanceof \ReflectionUnionType) {
             return array_map(
                function (ReflectionNamedType $type) use ($className): Type {
                    return new Type($type->getName(), $className);
                },
                $type->getTypes()
            );
        }

        return [];
    }
}
