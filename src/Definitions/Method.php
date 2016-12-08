<?php


namespace Triun\ModelBase\Definitions;


class Method
{
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
}