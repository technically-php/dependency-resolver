<?php

use Technically\ArrayContainer\ArrayContainer;
use Technically\DependencyResolver\DependencyResolver;

describe('DependencyResolver::resolve()', function () {
    it('should get an existing entry from container if possible', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);

        $container->set(DependencyResolver::class, $resolver);

        $resolved = $resolver->resolve(DependencyResolver::class);

        assert($resolved instanceof DependencyResolver);
        assert($resolved === $resolver);
    });

    it('should construct a new instance if container does not have it already', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);

        $resolved = $resolver->resolve(DependencyResolver::class);

        assert($resolved instanceof DependencyResolver);
        assert($resolved !== $resolver);
    });

    it('should throw InvalidArgumentException if the given class does not exist', function () {
        $resolver = new DependencyResolver();

        try {
            $resolver->resolve('Foo');
        } catch (Exception $exception) {
            // passthru
        }

        assert(isset($exception));
        assert($exception instanceof InvalidArgumentException);
        assert($exception->getMessage() === '`Foo` is not a valid class name.');
    });
});
