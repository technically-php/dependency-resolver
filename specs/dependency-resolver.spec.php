<?php

use Psr\Container\ContainerInterface;
use Technically\ArrayContainer\ArrayContainer;
use Technically\DependencyResolver\DependencyResolver;
use Technically\DependencyResolver\Exceptions\CannotAutowireDependencyArgument;
use Technically\DependencyResolver\Exceptions\ClassCannotBeInstantiated;
use Technically\DependencyResolver\Specs\Fixtures\MyAbstractClass;
use Technically\DependencyResolver\Specs\Fixtures\MyConcreteContainerService;
use Technically\DependencyResolver\Specs\Fixtures\MyAbstractContainerService;
use Technically\DependencyResolver\Specs\Fixtures\MyNullableArgumentService;
use Technically\DependencyResolver\Specs\Fixtures\MyOptionalArgumentService;
use Technically\DependencyResolver\Specs\Fixtures\MyUnresolvableScalarArgumentService;
use Technically\DependencyResolver\Specs\Fixtures\MyUntypedArgumentService;

describe('DependencyResolver', function() {
    it('should instantiate', function () {
        $resolver = new DependencyResolver(new ArrayContainer());

        assert($resolver instanceof DependencyResolver);
    });

    it('should instantiate a class using the bindings passed', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);

        $resolved = $resolver->resolve(MyAbstractContainerService::class, [
            'container' => $container,
        ]);

        assert($resolved instanceof MyAbstractContainerService);
        assert($resolved->container === $container);
    });

    it('should instantiate a class resolving dependencies from container, if possible', function () {
        $container = new ArrayContainer();
        $container->set(ContainerInterface::class, $container);
        $resolver = new DependencyResolver($container);

        $resolved = $resolver->resolve(MyAbstractContainerService::class);

        assert($resolved instanceof MyAbstractContainerService);
        assert($resolved->container === $container);
    });

    it('should instantiate a class creating required dependencies recursively', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);

        $resolved = $resolver->resolve(MyConcreteContainerService::class);

        assert($resolved instanceof MyConcreteContainerService);
        assert($resolved->container instanceof ArrayContainer);
        assert($resolved->container !== $container);
    });

    it('should instantiate a class falling back to default values, if possible', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);

        $resolved = $resolver->resolve(MyOptionalArgumentService::class);

        assert($resolved instanceof MyOptionalArgumentService);
        assert($resolved->name === 'MyOptionalArgumentService');
    });

    it('should instantiate a class falling back to null when there is no other choice', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);

        $resolved = $resolver->resolve(MyNullableArgumentService::class);

        assert($resolved instanceof MyNullableArgumentService);
        assert($resolved->container === null);
    });

    it('should throw exception if class being resolved is abstract', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);

        try {
            $resolver->resolve(MyAbstractClass::class);
        } catch (Exception $exception) {
            // passthru
        }

        assert(isset($exception));
        assert($exception instanceof ClassCannotBeInstantiated);
        assert($exception->getClassName() === MyAbstractClass::class);
    });

    it('should throw exception if dependency class cannot be autowired', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);

        try {
            $resolver->resolve(MyAbstractContainerService::class);
        } catch (Exception $exception) {
            // passthru
        }

        assert(isset($exception));
        assert($exception instanceof CannotAutowireDependencyArgument);
        assert($exception->getDependencyName() === MyAbstractContainerService::class);
        assert($exception->getArgumentName() === 'container');
        assert($exception->getMessage() === 'Could not autowire argument `container` for `Technically\DependencyResolver\Specs\Fixtures\MyAbstractContainerService`.');
    });

    it('should throw exception if scalar dependency cannot be autowired', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);

        try {
            $resolver->resolve(MyUnresolvableScalarArgumentService::class);
        } catch (Exception $exception) {
            // passthru
        }

        assert(isset($exception));
        assert($exception instanceof CannotAutowireDependencyArgument);
        assert($exception->getDependencyName() === MyUnresolvableScalarArgumentService::class);
        assert($exception->getArgumentName() === 'name');
        assert($exception->getMessage() === 'Could not autowire argument `name` for `Technically\DependencyResolver\Specs\Fixtures\MyUnresolvableScalarArgumentService`.');
    });

    it('should throw exception if untyped dependency cannot be autowired', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);

        try {
            $resolver->resolve(MyUntypedArgumentService::class);
        } catch (Exception $exception) {
            // passthru
        }

        assert(isset($exception));
        assert($exception instanceof CannotAutowireDependencyArgument);
        assert($exception->getDependencyName() === MyUntypedArgumentService::class);
        assert($exception->getArgumentName() === 'input');
        assert($exception->getMessage() === 'Could not autowire argument `input` for `Technically\DependencyResolver\Specs\Fixtures\MyUntypedArgumentService`.');
    });
});
