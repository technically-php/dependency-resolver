<?php

namespace Technically\DependencyResolver\Exceptions;

final class ClassCannotBeInstantiated extends DependencyResolutionException
{
    /**
     * @var string
     */
    private $className;

    public function __construct(string $className)
    {
        $this->className = $className;

        parent::__construct("Class (${className}) cannot be instantiated.");
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
