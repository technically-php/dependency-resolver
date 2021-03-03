<?php /** @noinspection PhpLanguageLevelInspection */

namespace Technically\DependencyResolver\Specs\Fixtures;

use Psr\Container\ContainerInterface;

final class MyUnionTypeDependencyService
{
    /**
     * @var callable|ContainerInterface|string
     */
    public $input;

    public function __construct(string|callable|ContainerInterface $input)
    {
        $this->input = $input;
    }
}
