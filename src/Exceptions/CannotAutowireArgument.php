<?php

namespace Technically\DependencyResolver\Exceptions;

use Throwable;

final class CannotAutowireArgument extends DependencyResolutionException
{
    /**
     * @var string
     */
    private string $argumentName;

    /**
     * @param string $argumentName
     * @param Throwable|null $previous
     */
    public function __construct(string $argumentName, Throwable $previous = null)
    {
        $this->argumentName = $argumentName;

        parent::__construct("Could not autowire argument `{$argumentName}`.", 0, $previous);
    }

    /**
     * @return string
     */
    public function getArgumentName(): string
    {
        return $this->argumentName;
    }
}
