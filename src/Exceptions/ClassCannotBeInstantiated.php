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
        parent::__construct("Class (${className}) cannot be instantiated.");
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
