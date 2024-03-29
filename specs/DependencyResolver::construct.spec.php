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
use Technically\DependencyResolver\Specs\Fixtures\MyParentDependencyService;
use Technically\DependencyResolver\Specs\Fixtures\MySelfDependencyService;
use Technically\DependencyResolver\Specs\Fixtures\MyUnionTypeDependencyService;
use Technically\DependencyResolver\Specs\Fixtures\MyUnresolvableScalarArgumentService;
use Technically\DependencyResolver\Specs\Fixtures\MyUntypedArgumentService;

describe('DependencyResolver::construct()', function () {
    it('should instantiate a class using the bindings passed', function () {
        $resolver = new DependencyResolver();
        $container = new ArrayContainer();

        $resolved = $resolver->construct(MyAbstractContainerService::class, [
            'container' => $container,
        ]);

        assert($resolved instanceof MyAbstractContainerService);
        assert($resolved->container === $container);
    });

    it('should instantiate a class resolving dependencies from container, if possible', function () {
        $container = new ArrayContainer();
        $container->set(ContainerInterface::class, $container);
        $resolver = new DependencyResolver($container);

        $resolved = $resolver->construct(MyAbstractContainerService::class);

        assert($resolved instanceof MyAbstractContainerService);
        assert($resolved->container === $container);
    });

    it('should instantiate a class resolving self type dependency', function () {
        $service = new MySelfDependencyService();

        $container = new ArrayContainer();
        $container->set(MySelfDependencyService::class, $service);

        $resolver = new DependencyResolver($container);

        $resolved = $resolver->construct(MySelfDependencyService::class);

        assert($resolved instanceof MySelfDependencyService);
        assert($resolved !== $service);
        assert($resolved->parent instanceof MySelfDependencyService);
        assert($resolved->parent === $service);
    });

    it('should instantiate a class resolving parent type dependency', function () {
        $service = new MySelfDependencyService();

        $container = new ArrayContainer();
        $container->set(MySelfDependencyService::class, $service);

        $resolver = new DependencyResolver($container);

        $resolved = $resolver->construct(MyParentDependencyService::class);

        assert($resolved instanceof MyParentDependencyService);
        assert($resolved->parent instanceof MySelfDependencyService);
        assert($resolved->parent === $service);
    });

    if (PHP_MAJOR_VERSION >= 8) {
        it('should instantiate a class resolving union type dependencies', function () {
            $container = new ArrayContainer();
            $container->set(ContainerInterface::class, $container);
            $resolver = new DependencyResolver($container);

            $resolved = $resolver->construct(MyUnionTypeDependencyService::class);

            assert($resolved instanceof MyUnionTypeDependencyService);
            assert($resolved->input === $container);
        });
    }

    it('should instantiate a class creating required dependencies recursively', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);

        $resolved = $resolver->construct(MyConcreteContainerService::class);

        assert($resolved instanceof MyConcreteContainerService);
        assert($resolved->container instanceof ArrayContainer);
        assert($resolved->container !== $container);
    });

    it('should instantiate a class falling back to default values, if possible', function () {
        $resolver = new DependencyResolver();

        $resolved = $resolver->construct(MyOptionalArgumentService::class);

        assert($resolved instanceof MyOptionalArgumentService);
        assert($resolved->name === 'MyOptionalArgumentService');
    });

    it('should instantiate a class falling back to null when there is no other choice', function () {
        $resolver = new DependencyResolver();

        $resolved = $resolver->construct(MyNullableArgumentService::class);

        assert($resolved instanceof MyNullableArgumentService);
        assert($resolved->container === null);
    });

    it('should throw exception if class being resolved is abstract', function () {
        $resolver = new DependencyResolver();

        try {
            $resolver->construct(MyAbstractClass::class);
        } catch (Exception $exception) {
            // passthru
        }

        assert(isset($exception));
        assert($exception instanceof ClassCannotBeInstantiated);
        assert($exception->getClassName() === MyAbstractClass::class);
    });

    it('should throw exception if dependency class cannot be autowired', function () {
        $resolver = new DependencyResolver();

        try {
            $resolver->construct(MyAbstractContainerService::class);
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
        $resolver = new DependencyResolver();

        try {
            $resolver->construct(MyUnresolvableScalarArgumentService::class);
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
        $resolver = new DependencyResolver();

        try {
            $resolver->construct(MyUntypedArgumentService::class);
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
