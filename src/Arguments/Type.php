<?php

namespace Technically\DependencyResolver\Arguments;

use InvalidArgumentException;

final class Type
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    private $class;

    /**
     * @param string $type
     * @param string|null $class
     */
    public function __construct(string $type, string $class = null)
    {
        if ($type === 'self' && empty($class)) {
            throw new InvalidArgumentException('Type-hint `self` can only be used inside classes.');
        }
        if ($type === 'parent' && empty($class)) {
            throw new InvalidArgumentException('Type-hint `parent` can only be used inside classes.');
        }

        $this->type = $type;
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isNull(): bool
    {
        return $this->type === 'null';
    }

    /**
     * @return bool
     */
    public function isScalar(): bool
    {
        return $this->type === 'null'
            || $this->type === 'false'
            || $this->type === 'bool'
            || $this->type === 'int'
            || $this->type === 'float'
            || $this->type === 'string';
    }

    /**
     * @return bool
     */
    public function isArray(): bool
    {
        return $this->type === 'array';
    }

    /**
     * @return bool
     */
    public function isIterable(): bool
    {
        return $this->type === 'iterable';
    }

    /**
     * @return bool
     */
    public function isMixed(): bool
    {
        return $this->type === 'mixed';
    }

    /**
     * @return bool
     */
    public function isObject(): bool
    {
        return $this->type === 'object';
    }

    /**
     * @return bool
     */
    public function isCallable(): bool
    {
        return $this->type === 'callable';
    }

    /**
     * @return bool
     */
    public function isParent(): bool
    {
        return $this->type === 'parent';
    }

    /**
     * @return bool
     */
    public function isSelf(): bool
    {
        return $this->type === 'self';
    }

    /**
     * @return bool
     */
    public function isClassName(): bool
    {
        return ! $this->isScalar()
            && ! $this->isArray()
            && ! $this->isIterable()
            && ! $this->isObject()
            && ! $this->isMixed()
            && ! $this->isCallable()
            && ! $this->isParent()
            && ! $this->isSelf();
    }

    /**
     * @return bool
     */
    public function isClassReference(): bool
    {
        return $this->isParent() || $this->isSelf() || $this->isClassName();
    }

    /**
     * Get classname this type hint is referring to.
     *
     * This will also resolve `self` and `parent` hints that can be used inside classes.
     * @see https://www.php.net/manual/en/language.types.declarations.php
     *
     * @return string|null
     */
    public function getClassName(): ?string
    {
        if ($this->isSelf()) {
            return $this->class;
        }

        if ($this->isParent()) {
            return get_parent_class($this->class);
        }

        if ($this->isClassName()) {
            return $this->type;
        }

        return null;
    }
}
