<?php

namespace Technically\DependencyResolver\Arguments;

final class Argument
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Type[]
     */
    private $types;

    /**
     * @var bool
     */
    private $optional;

    /**
     * @var bool
     */
    private $nullable;

    /**
     * @var mixed|null
     */
    private $default;

    /**
     * @param string $name
     * @param Type[] $types
     * @param bool $optional
     * @param bool $nullable
     * @param mixed|null $default
     */
    public function __construct(
        string $name,
        array $types,
        bool $optional = false,
        bool $nullable = false,
        $default = null
    ) {
        $this->name = $name;
        $this->types = (function (Type...$types): array {
            return $types;
        })(...$types);
        $this->optional = $optional;
        $this->nullable = $nullable;
        $this->default = $default;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Type[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @return bool
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->default;
    }
}
