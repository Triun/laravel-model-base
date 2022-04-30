<?php

namespace Triun\ModelBase\Definitions;

use Triun\ModelBase\Utils\SkeletonUtil;

class Method
{
    public string $file;

    /**
     * @var integer[]
     */
    public array $line;
    public string $name;

    /**
     * @var string[]
     */
    public array $modifiers;
    public ?int $modifiers_id = null;
    public mixed $default = null;
    public mixed $value = null;
    public string $docComment;

    public function isDirty(): bool
    {
        return $this->value !== $this->default;
    }

    public function setValue(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getFileName(): string
    {
        return $this->file;
    }

    public function getStartLine(): int
    {
        return $this->line[0];
    }

    public function getEndLine(): int
    {
        return $this->line[1];
    }

    public function getDocComment(): string
    {
        return $this->docComment;
    }

    /**
     * Load method content from the original file.
     */
    public function load(): void
    {
        SkeletonUtil::loadMethodValue($this);
    }

    /**
     * Append code at the end of the method.
     */
    public function append(string $code): void
    {
        SkeletonUtil::appendToMethod($this, $code);
    }
}
