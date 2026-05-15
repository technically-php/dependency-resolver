<?php

use Psr\Container\ContainerInterface;
use Technically\ArrayContainer\ArrayContainer;
use Technically\DependencyResolver\DependencyResolver;
use Technically\DependencyResolver\Exceptions\CannotAutowireDependencyArgument;
use Technically\DependencyResolver\Exceptions\ClassCannotBeInstantiated;
use Technically\DependencyResolver\Tests\Fixtures\MyAbstractClass;
use Technically\DependencyResolver\Tests\Fixtures\MyConcreteContainerService;
use Technically\DependencyResolver\Tests\Fixtures\MyAbstractContainerService;
use Technically\DependencyResolver\Tests\Fixtures\MyNullableArgumentService;
use Technically\DependencyResolver\Tests\Fixtures\MyOptionalArgumentService;
use Technically\DependencyResolver\Tests\Fixtures\MyParentDependencyService;
use Technically\DependencyResolver\Tests\Fixtures\MySelfDependencyService;
use Technically\DependencyResolver\Tests\Fixtures\MyUnionTypeDependencyService;
use Technically\DependencyResolver\Tests\Fixtures\MyUnresolvableScalarArgumentService;
use Technically\DependencyResolver\Tests\Fixtures\MyUntypedArgumentService;

describe('DependencyResolver::construct()', function () {
    it('should instantiate a class using the bindings passed', function () {
        $resolver = new DependencyResolver();
        $container = new ArrayContainer();

        $resolved = $resolver->construct(MyAbstractContainerService::class, [
            'container' => $container,
        ]);

        expect($resolved)->toBeInstanceOf(MyAbstractContainerService::class);
        expect($resolved->container)->toBe($container);
    });

    it('should instantiate a class resolving dependencies from container, if possible', function () {
        $container = new ArrayContainer();
        $container->set(ContainerInterface::class, $container);
        $resolver = new DependencyResolver($container);

        $resolved = $resolver->construct(MyAbstractContainerService::class);

        expect($resolved)->toBeInstanceOf(MyAbstractContainerService::class);
        expect($resolved->container)->toBe($container);
    });

    it('should instantiate a class resolving self type dependency', function () {
        $service = new MySelfDependencyService();

        $container = new ArrayContainer();
        $container->set(MySelfDependencyService::class, $service);

        $resolver = new DependencyResolver($container);

        $resolved = $resolver->construct(MySelfDependencyService::class);

        expect($resolved)->toBeInstanceOf(MySelfDependencyService::class);
        expect($resolved)->not->toBe($service);
        expect($resolved->parent)->toBeInstanceOf(MySelfDependencyService::class);
        expect($resolved->parent)->toBe($service);
    });

    it('should instantiate a class resolving parent type dependency', function () {
        $service = new MySelfDependencyService();

        $container = new ArrayContainer();
        $container->set(MySelfDependencyService::class, $service);

        $resolver = new DependencyResolver($container);

        $resolved = $resolver->construct(MyParentDependencyService::class);

        expect($resolved)->toBeInstanceOf(MyParentDependencyService::class);
        expect($resolved->parent)->toBeInstanceOf(MySelfDependencyService::class);
        expect($resolved->parent)->toBe($service);
    });

    if (PHP_MAJOR_VERSION >= 8) {
        it('should instantiate a class resolving union type dependencies', function () {
            $container = new ArrayContainer();
            $container->set(ContainerInterface::class, $container);
            $resolver = new DependencyResolver($container);

            $resolved = $resolver->construct(MyUnionTypeDependencyService::class);

            expect($resolved)->toBeInstanceOf(MyUnionTypeDependencyService::class);
            expect($resolved->input)->toBe($container);
        });
    }

    it('should instantiate a class creating required dependencies recursively', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);

        $resolved = $resolver->construct(MyConcreteContainerService::class);

        expect($resolved)->toBeInstanceOf(MyConcreteContainerService::class);
        expect($resolved->container)->toBeInstanceOf(ArrayContainer::class);
        expect($resolved->container)->not->toBe($container);
    });

    it('should instantiate a class falling back to default values, if possible', function () {
        $resolver = new DependencyResolver();

        $resolved = $resolver->construct(MyOptionalArgumentService::class);

        expect($resolved)->toBeInstanceOf(MyOptionalArgumentService::class);
        expect($resolved->name)->toBe('MyOptionalArgumentService');
    });

    it('should instantiate a class falling back to null when there is no other choice', function () {
        $resolver = new DependencyResolver();

        $resolved = $resolver->construct(MyNullableArgumentService::class);

        expect($resolved)->toBeInstanceOf(MyNullableArgumentService::class);
        expect($resolved->container)->toBeNull();
    });

    it('should throw exception if class being resolved is abstract', function () {
        $resolver = new DependencyResolver();

        try {
            $resolver->construct(MyAbstractClass::class);
        } catch (Exception $exception) {
            // passthru
        }

        expect(isset($exception))->toBeTrue();
        expect($exception)->toBeInstanceOf(ClassCannotBeInstantiated::class);
        expect($exception->getClassName())->toBe(MyAbstractClass::class);
    });

    it('should throw exception if dependency class cannot be autowired', function () {
        $resolver = new DependencyResolver();

        try {
            $resolver->construct(MyAbstractContainerService::class);
        } catch (Exception $exception) {
            // passthru
        }

        expect(isset($exception))->toBeTrue();
        expect($exception)->toBeInstanceOf(CannotAutowireDependencyArgument::class);
        expect($exception->getDependencyName())->toBe(MyAbstractContainerService::class);
        expect($exception->getArgumentName())->toBe('container');
        expect($exception->getMessage())->toBe('Could not autowire argument `container` for `Technically\DependencyResolver\Tests\Fixtures\MyAbstractContainerService`.');
    });

    it('should throw exception if scalar dependency cannot be autowired', function () {
        $resolver = new DependencyResolver();

        try {
            $resolver->construct(MyUnresolvableScalarArgumentService::class);
        } catch (Exception $exception) {
            // passthru
        }

        expect(isset($exception))->toBeTrue();
        expect($exception)->toBeInstanceOf(CannotAutowireDependencyArgument::class);
        expect($exception->getDependencyName())->toBe(MyUnresolvableScalarArgumentService::class);
        expect($exception->getArgumentName())->toBe('name');
        expect($exception->getMessage())->toBe('Could not autowire argument `name` for `Technically\DependencyResolver\Tests\Fixtures\MyUnresolvableScalarArgumentService`.');
    });

    it('should throw exception if untyped dependency cannot be autowired', function () {
        $resolver = new DependencyResolver();

        try {
            $resolver->construct(MyUntypedArgumentService::class);
        } catch (Exception $exception) {
            // passthru
        }

        expect(isset($exception))->toBeTrue();
        expect($exception)->toBeInstanceOf(CannotAutowireDependencyArgument::class);
        expect($exception->getDependencyName())->toBe(MyUntypedArgumentService::class);
        expect($exception->getArgumentName())->toBe('input');
        expect($exception->getMessage())->toBe('Could not autowire argument `input` for `Technically\DependencyResolver\Tests\Fixtures\MyUntypedArgumentService`.');
    });
});
