<?php

namespace Triun\ModelBase\Definitions;

class Constant
{
    /**
     * @var string
     */
    public $name;

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
}
