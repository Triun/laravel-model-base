<?php

namespace Triun\ModelBase\Definitions;

class Constant
{
    public string $name;
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
