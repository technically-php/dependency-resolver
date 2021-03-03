<?php

use Psr\Container\ContainerInterface;
use Technically\ArrayContainer\ArrayContainer;
use Technically\DependencyResolver\DependencyResolver;
use Technically\DependencyResolver\Specs\Fixtures\MyConcreteContainerService;
use Technically\DependencyResolver\Specs\Fixtures\MyAbstractContainerService;

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
});
