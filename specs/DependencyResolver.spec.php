<?php

use Technically\DependencyResolver\DependencyResolver;

describe('DependencyResolver', function () {
    it('should instantiate', function () {
        $resolver = new DependencyResolver();

        assert($resolver instanceof DependencyResolver);
    });
});
