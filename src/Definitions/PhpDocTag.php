<?php

namespace Triun\ModelBase\Definitions;

class PhpDocTag
{
    protected ?string $name;
    public ?string $tag = null;
    public ?string $type = null;
    public ?string $description = null;

    public function __construct(?string $name, ?string $tag = null, ?string $type = null, ?string $description = null)
    {
        $this->name = $name;
        $this->tag = $tag;
        $this->type = $type;
        $this->description = $description;
    }

    public function hasName(): bool
    {
        return null !== $this->name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
