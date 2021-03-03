<?php

namespace Technically\DependencyResolver\Specs\Fixtures;

final class MyUntypedArgumentService
{
    public $input;

    public function __construct($input)
    {
        $this->input = $input;
    }
}
