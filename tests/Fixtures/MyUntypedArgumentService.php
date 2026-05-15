<?php

namespace Technically\DependencyResolver\Tests\Fixtures;

final class MyUntypedArgumentService
{
    public $input;

    public function __construct($input)
    {
        $this->input = $input;
    }
}
