<?php

use Psr\Container\ContainerInterface;
use Technically\ArrayContainer\ArrayContainer;
use Technically\DependencyResolver\DependencyResolver;
use Technically\DependencyResolver\Tests\Fixtures\MyInstanceMethodService;
use Technically\DependencyResolver\Tests\Fixtures\MyInvokableService;
use Technically\DependencyResolver\Tests\Fixtures\MyStaticMethodService;
use Technically\NullContainer\NullContainer;

describe('DependencyResolver::call()', function () {
    it('should call global functions by name', function () {
        require __DIR__ . '/Fixtures/my_global_function.php';

        $resolver = new DependencyResolver();

        $value = $resolver->call('my_global_function', ['value' => $resolver]);

        expect($value)->toBeTrue();
    });

    it('should call Closures resolving dependencies', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);
        $container->set(DependencyResolver::class, $resolver);

        [$r, $m, $n] = $resolver->call(function (DependencyResolver $resolver, string $message, ?int $else = null) {
            return [$resolver, $message, $else];
        }, [
            'message' => 'Hello'
        ]);

        expect($r)->toBe($resolver);
        expect($m)->toBe('Hello');
        expect($n)->toBeNull();
    });

    it('should call static method array resolving dependencies', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);
        $container->set(DependencyResolver::class, $resolver);

        [$r, $m, $n] = $resolver->call([MyStaticMethodService::class, 'test'], [
            'message' => 'Hello'
        ]);

        expect($r)->toBe($resolver);
        expect($m)->toBe('Hello');
        expect($n)->toBeNull();
    });

    it('should call static method string resolving dependencies', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);
        $container->set(DependencyResolver::class, $resolver);

        [$r, $m, $n] = $resolver->call(MyStaticMethodService::class . '::test', [
            'message' => 'Hello'
        ]);

        expect($r)->toBe($resolver);
        expect($m)->toBe('Hello');
        expect($n)->toBeNull();
    });

    it('should call instance methods resolving dependencies', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);
        $container->set(DependencyResolver::class, $resolver);

        [$r, $m, $n] = $resolver->call([new MyInstanceMethodService(), 'test'], [
            'message' => 'Hello'
        ]);

        expect($r)->toBe($resolver);
        expect($m)->toBe('Hello');
        expect($n)->toBeNull();
    });

    it('should call invokable objects resolving dependencies', function () {
        $container = new ArrayContainer();
        $resolver = new DependencyResolver($container);
        $container->set(DependencyResolver::class, $resolver);

        [$r, $m, $n] = $resolver->call(new MyInvokableService(), [
            'message' => 'Hello'
        ]);

        expect($r)->toBe($resolver);
        expect($m)->toBe('Hello');
        expect($n)->toBeNull();
    });

    it('should resolve typed variadic parameters', function () {
        $container = new ArrayContainer();
        $container->set(ContainerInterface::class, $container);
        $resolver = new DependencyResolver($container);

        $ret = $resolver->call(function (ContainerInterface ...$containers) {
            return $containers;
        });

        expect($ret)->toBeArray();
        expect($ret)->toHaveCount(1);
    });

    it('should call function by passing variadic parameter value explicitly', function () {
        $container = new ArrayContainer();
        $container->set(ContainerInterface::class, $container);
        $resolver = new DependencyResolver($container);

        $null = new NullContainer();

        $ret = $resolver->call(function (ContainerInterface ...$containers) {
            return $containers;
        }, [
            'containers' => [$null],
        ]);

        expect($ret)->toBeArray();
        expect($ret)->toHaveCount(1);
        expect($ret)->toBe([$null]);
    });
});
