<?php

namespace Triun\ModelBase\Definitions;

use Exception;

/**
 * Class Skeleton
 * @package Triun\ModelBase\Definitions
 */
class Skeleton
{
    /**
     * Name for the class, included namespace.
     *
     * @var string
     */
    public $className;

    /**
     * Which class extends.
     *
     * @var string
     */
    public $extends;

    /**
     * Which classes should be imported.
     *
     * @var string[]
     */
    public $uses = [];

    /**
     * Which interfaces implements.
     *
     * @var string[]
     */
    public $interfaces = [];

    /**
     * Which traits uses.
     *
     * @var string[]
     */
    protected $traits = [];

    /**
     * @var string
     */
    public $phpDocComment;

    /**
     * Class PhpDoc tags
     *
     * @var PhpDocTag[]
     */
    protected $phpDocTags = [];

    /**
     * Model Base Skeleton constants.
     *
     * @var \Triun\ModelBase\Definitions\Constant[]
     */
    protected $constants = [];

    /**
     * Model Base Skeleton properties.
     *
     * @var \Triun\ModelBase\Definitions\Property[]
     */
    protected $properties = [];

    /**
     * Model Base Skeleton methods.
     *
     * @var \Triun\ModelBase\Definitions\Method[]
     */
    protected $methods = [];

    /**
     * Get the full namespace name.
     *
     * @return string
     */
    protected function getNamespace()
    {
        return trim(implode('\\', array_slice(explode('\\', $this->className), 0, -1)), '\\');
    }

    /**
     * Get the class "basename".
     *
     * @return string
     */
    protected function getClassBasename()
    {
        return class_basename($this->className);
    }

    /**
     * Get the extends class "basename".
     *
     * @return string
     */
    protected function getExtendsBasename()
    {
        return class_basename($this->className);
    }

    /**
     * @return string[]
     */
    public function interfaces()
    {
        return $this->interfaces;
    }

    /**
     * @return string[]
     */
    public function traits()
    {
        return $this->traits;
    }

    /**
     * @return \Triun\ModelBase\Definitions\PhpDocTag[]
     */
    public function phpDocTags()
    {
        return $this->phpDocTags;
    }

    /**
     * @return \Triun\ModelBase\Definitions\Constant[]
     */
    public function constants()
    {
        return $this->constants;
    }

    /**
     * @return \Triun\ModelBase\Definitions\Property[]
     */
    public function properties()
    {
        return $this->properties;
    }

    /**
     * @return \Triun\ModelBase\Definitions\Method[]
     */
    public function methods()
    {
        return $this->methods;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasInterface($key)
    {
        return isset($this->interfaces[$key]);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasTrait($key)
    {
        return isset($this->traits[$key]);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasPhpDocTag($key)
    {
        return isset($this->phpDocTags[$key]);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasConstant($key)
    {
        return isset($this->constants[$key]);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasProperty($key)
    {
        return isset($this->properties[$key]);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasMethod($key)
    {
        return isset($this->methods[$key]);
    }

    /**
     * Get constant.
     *
     * @param  string  $key
     *
     * @return  \Triun\ModelBase\Definitions\Constant|null
     * @throws \Exception
     */
    public function constant($key)
    {
        if (!$this->hasConstant($key)) {
            throw new Exception("Constant $key not defined");
        }
        return $this->constants[$key];
    }

    /**
     * Get property.
     *
     * @param  string $key
     *
     * @return null|Property
     * @throws \Exception
     */
    public function property($key)
    {
        if (!$this->hasProperty($key)) {
            throw new Exception("Property $key not defined");
        }
        return $this->properties[$key];
    }

    /**
     * Get method.
     *
     * @param  string  $key
     *
     * @return  \Triun\ModelBase\Definitions\Method|null
     * @throws \Exception
     */
    public function method($key)
    {
        if (!$this->hasMethod($key)) {
            throw new Exception("Method $key not defined");
        }
        return $this->methods[$key];
    }

    /**
     * Add Interface to be implemented.
     *
     * @param  string $interface
     *
     * @throws \Exception
     */
    public function addInterface($interface)
    {
        $this->interfaces[$interface] = $interface;
    }

    /**
     * Add Trait.
     *
     * @param  string $traitName
     *
     * @throws \Exception
     */
    public function addTrait($traitName)
    {
//        if ( array_search($traitName, get_declared_traits()) === false ) {
//            //dump(get_declared_traits());
//            throw new Exception("$traitName is not a valid trait");
//        }

        $this->traits[$traitName] = $traitName;
    }

    /**
     * Add phpDoc Tag.
     *
     * @param  \Triun\ModelBase\Definitions\PhpDocTag $value
     */
    public function addPhpDocTag(PhpDocTag $value)
    {
        $this->phpDocTags[$value->getName()] = $value;
    }

    /**
     * Set Constant.
     *
     * @param  \Triun\ModelBase\Definitions\Constant $value
     */
    public function addConstant(Constant $value)
    {
        $this->constants[$value->name] = $value;
    }

    /**
     * Set Property.
     *
     * @param  \Triun\ModelBase\Definitions\Property $value
     */
    public function addProperty(Property $value)
    {
        $this->properties[$value->name] = $value;
    }

    /**
     * Set Method.
     *
     * @param  \Triun\ModelBase\Definitions\Method $value
     */
    public function addMethod(Method $value)
    {
        $this->methods[$value->name] = $value;
    }

    /**
     * Unset Constant.
     *
     * @param  string  $key
     */
    public function removeConstant($key)
    {
        unset($this->constants[$key]);
    }

    /**
     * Unset Property.
     *
     * @param  string  $key
     */
    public function removeProperty($key)
    {
        unset($this->properties[$key]);
    }

    /**
     * Unset Method.
     *
     * @param  string  $key
     */
    public function removeMethod($key)
    {
        unset($this->methods[$key]);
    }

    /**
     * @return Constant[]
     */
    public function dirtyConstants()
    {
        return array_filter($this->constants, function ($value) {
            /* @var $value Constant */
            return  $value->isDirty();
        });
    }

    /**
     * @return Property[]
     */
    public function dirtyProperties()
    {
        return array_filter($this->properties, function ($value) {
            /* @var $value Property */
            return  $value->isDirty();
        });
    }

    /**
     * @return Method[]
     */
    public function dirtyMethods()
    {
        return array_filter($this->methods, function ($value) {
            /* @var $value Property */
            return  $value->isDirty();
        });
    }

    /**
     * Get the skeleton as an array, with the model base constants, properties and methods.
     *
     * @return  array
     */
    public function toArray()
    {
        return [
            'constants' => $this->constants,
            'properties' => $this->properties,
            'methods' => $this->methods,
        ];
    }

    /**
     * Get the values of the skeleton object.
     *
     * @return  array
     */
    public function defaults()
    {
        return [
            'constants' => array_map(function ($value) {
                return  $value->default;
            }, $this->constants),
            'properties' => array_map(function ($value) {
                return  $value->default;
            }, $this->properties),
            'methods' => $this->methods,
        ];
    }

    /**
     * Get the values of the skeleton object.
     *
     * @return  array
     */
    public function values()
    {
        return [
            'constants' => array_map(function ($value) {
                return  $value->value;
            }, $this->constants),
            'properties' => array_map(function ($value) {
                return  $value->value;
            }, $this->properties),
            'methods' => $this->methods,
        ];
    }

    /**
     * Get the dirty items.
     *
     * @return  array
     */
    public function dirty()
    {
        return [
            'constants' => array_filter($this->constants, function ($value) {
                /* @var $value Constant */
                return  $value->isDirty();
            }),
            'properties' => array_filter($this->properties, function ($value) {
                /* @var $value Constant */
                return  $value->isDirty();
            }),
            'methods' => $this->methods,
        ];
    }
}
