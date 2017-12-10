<?php

namespace Triun\ModelBase\Definitions;

/**
 * Class PhpDocTag
 *
 * @package Triun\ModelBase\Definitions
 */
class PhpDocTag
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    public $tag;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $description;

    /**
     * PhpDocTag constructor.
     *
     * @param string $name
     * @param string $tag
     * @param string $type
     * @param string $description
     */
    public function __construct($name, $tag = null, $type = null, $description = null)
    {
        $this->name = $name;
        $this->tag = $tag;
        $this->type = $type;
        $this->description = $description;
    }

    /**
     * @return boolean
     */
    public function hasName()
    {
        return $this->name !== null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
