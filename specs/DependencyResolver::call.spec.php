<?php

use Technically\ArrayContainer\ArrayContainer;
use Technically\DependencyResolver\DependencyResolver;
use Technically\DependencyResolver\Specs\Fixtures\MyInstanceMethodService;
use Technically\DependencyResolver\Specs\Fixtures\MyInvokableService;
use Technically\DependencyResolver\Specs\Fixtures\MyStaticMethodService;

describe('DependencyResolver::call()', function () {
    it('should call global functions by name', function () {
        $resolver = new DependencyResolver();

        $value = $resolver->call('is_object', ['value' => $resolver]);

        assert($value === true);
    });

    it('should call Closures resolving dependencies', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);
        $container->set(DependencyResolver::class, $resolver);

        [$r, $m, $n] = $resolver->call(function (DependencyResolver $resolver, string $message, int $else = null) {
            return [$resolver, $message, $else];
        }, [
            'message' => 'Hello'
        ]);

        assert($r === $resolver);
        assert($m === 'Hello');
        assert($n === null);
    });

    it('should call static method array resolving dependencies', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);
        $container->set(DependencyResolver::class, $resolver);

        [$r, $m, $n] = $resolver->call([MyStaticMethodService::class, 'test'], [
            'message' => 'Hello'
        ]);

        assert($r === $resolver);
        assert($m === 'Hello');
        assert($n === null);
    });

    it('should call static method string resolving dependencies', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);
        $container->set(DependencyResolver::class, $resolver);

        [$r, $m, $n] = $resolver->call(MyStaticMethodService::class . '::test', [
            'message' => 'Hello'
        ]);

        assert($r === $resolver);
        assert($m === 'Hello');
        assert($n === null);
    });

    it('should call instance methods resolving dependencies', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);
        $container->set(DependencyResolver::class, $resolver);

        [$r, $m, $n] = $resolver->call([new MyInstanceMethodService(), 'test'], [
            'message' => 'Hello'
        ]);

        assert($r === $resolver);
        assert($m === 'Hello');
        assert($n === null);
    });

    it('should call invokable objects resolving dependencies', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);
        $container->set(DependencyResolver::class, $resolver);

        [$r, $m, $n] = $resolver->call(new MyInvokableService(), [
            'message' => 'Hello'
        ]);

        assert($r === $resolver);
        assert($m === 'Hello');
        assert($n === null);
    });
});
