<?php

namespace Triun\ModelBase\Definitions;

class Property
{
    public string $name;

    /**
     * @var string[]
     */
    public array $modifiers;
    public int $modifiers_id;
    public mixed $default;
    public mixed $value;
    public string $docComment;

    public function isDirty(): bool
    {
        return $this->value !== $this->default;
    }

    public function setValue($value): static
    {
        $this->value = $value;

        return $this;
    }
}
