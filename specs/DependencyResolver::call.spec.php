<?php

use Psr\Container\ContainerInterface;
use Technically\ArrayContainer\ArrayContainer;
use Technically\DependencyResolver\DependencyResolver;
use Technically\DependencyResolver\Specs\Fixtures\MyInstanceMethodService;
use Technically\DependencyResolver\Specs\Fixtures\MyInvokableService;
use Technically\DependencyResolver\Specs\Fixtures\MyStaticMethodService;

describe('DependencyResolver::call()', function () {
    it('should call global functions by name', function () {
        require __DIR__ . '/Fixtures/my_global_function.php';

        $resolver = new DependencyResolver();

        $value = $resolver->call('my_global_function', ['value' => $resolver]);

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

    it('should resolve typed variadic parameters', function () {
        $container = new ArrayContainer();
        $container->set(ContainerInterface::class, $container);
        $resolver = new DependencyResolver($container);

        $ret = $resolver->call(function (ContainerInterface ...$containers) {
            return $containers;
        });

        assert(is_array($ret));
        assert(count($ret) === 1);
    });
});
