<?php

namespace Triun\ModelBase\Definitions;

use Triun\ModelBase\Utils\SkeletonUtil;

class Method
{
    /**
     * @var string
     */
    public $file;

    /**
     * @var integer[]
     */
    public $line;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string[]
     */
    public $modifiers;

    /**
     * @var int
     */
    public $modifiers_id;

    /**
     * @var mixed
     */
    public $default;

    /**
     * @var mixed
     */
    public $value;

    /**
     * @var string
     */
    public $docComment;

    /**
     * @return bool
     */
    public function isDirty()
    {
        return $this->value !== $this->default;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getStartLine()
    {
        return $this->line[0];
    }

    /**
     * @return int
     */
    public function getEndLine()
    {
        return $this->line[1];
    }

    /**
     * @return string
     */
    public function getDocComment()
    {
        return $this->docComment;
    }

    /**
     * Load method content from the original file.
     */
    public function load()
    {
        SkeletonUtil::loadMethodValue($this);
    }

    /**
     * Append code at the end of the method.
     *
     * @param string $code
     */
    public function append($code)
    {
        SkeletonUtil::appendToMethod($this, $code);
    }
}
