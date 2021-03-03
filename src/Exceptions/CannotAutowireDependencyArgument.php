<?php

namespace Technically\DependencyResolver\Exceptions;

use Throwable;

final class CannotAutowireDependencyArgument extends DependencyResolutionException
{
    /**
     * @var string
     */
    private $dependencyName;

    /**
     * @var string
     */
    private $argumentName;

    /**
     * @param string $dependencyName
     * @param string $argumentName
     * @param Throwable|null $previous
     */
    public function __construct(string $dependencyName, string $argumentName, Throwable $previous = null)
    {
        $this->dependencyName = $dependencyName;
        $this->argumentName = $argumentName;

        parent::__construct("Could not autowire argument `{$argumentName}` for `${dependencyName}`.", 0, $previous);
    }

    /**
     * @return string
     */
    public function getDependencyName(): string
    {
        return $this->dependencyName;
    }

    /**
     * @return string
     */
    public function getArgumentName(): string
    {
        return $this->argumentName;
    }
}
