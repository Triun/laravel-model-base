<?php

namespace Triun\ModelBase\Definitions;

use InvalidArgumentException;
use Triun\ModelBase\Exception\SkeletonUseAliasException;
use Triun\ModelBase\Exception\SkeletonUseNameException;

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
    protected $uses = [];

    /**
     * Which interfaces implements.
     *
     * @var string[]
     */
    protected $interfaces = [];

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
    public function uses()
    {
        // return $this->uses;
        $uses = [];

        // Add the extended
        $uses[$this->extends] = $this->extends;

        // Add the implements
        $this->usesCompilationAppend($uses, $this->interfaces);

        // Add the traits
        $this->usesCompilationAppend($uses, $this->traits);

        // Add other uses
        $this->usesCompilationAppend($uses, $this->uses);

        // Fix duplicated basenames to be used (from different namespaces).
        /*$basenames = [];
        foreach ($uses as $use) {
            $basename = class_basename($use);
            if (isset($basenames[$basename])) {
                $count = 0;
                do {
                    $count++;
                    $altBasename = $basename.'_'.$count;
                } while (isset($basenames[$altBasename]));
                $basenames[$altBasename] = $use;

                $uses[$use] = "$use as $altBasename";
            } else {
                $basenames[$basename] = $use;
            }
        }*/

        return $uses;
    }

    protected function usesCompilationAppend(&$uses, array $names)
    {
        foreach ($names as $name => $alias) {
            if (isset($uses[$alias])) {
                throw new SkeletonUseNameException($name, $alias, $uses[$alias]);
            }

            if (in_array($name, $uses)) {
                throw new SkeletonUseAliasException($alias, $name, array_search($name, $uses));
            }

            if (class_basename($name) === $alias) {
                $uses[$alias] = $name;
            } else {
                $uses[$alias] = $name . ' as ' . $alias;
            }
        }
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
    public function hasUse($key)
    {
        return isset($this->uses[$key])
            || isset($this->extends[$key])
            || isset($this->interfaces[$key])
            || isset($this->traits[$key]);
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
     * @throws \InvalidArgumentException
     */
    public function constant($key)
    {
        if (!$this->hasConstant($key)) {
            throw new InvalidArgumentException("Constant $key not defined");
        }

        return $this->constants[$key];
    }

    /**
     * Get property.
     *
     * @param  string $key
     *
     * @return null|Property
     * @throws \InvalidArgumentException
     */
    public function property($key)
    {
        if (!$this->hasProperty($key)) {
            throw new InvalidArgumentException("Property $key not defined");
        }
        return $this->properties[$key];
    }

    /**
     * Get method.
     *
     * @param  string  $key
     *
     * @return  \Triun\ModelBase\Definitions\Method|null
     * @throws \InvalidArgumentException
     */
    public function method($key)
    {
        if (!$this->hasMethod($key)) {
            throw new InvalidArgumentException("Method $key not defined");
        }
        return $this->methods[$key];
    }

    /**
     * Add Use.
     *
     * @param  string       $className
     * @param  string|null  $alias
     */
    public function addUse($className, $alias = null)
    {
        // $this->uses[$className] = $alias?: basename($className);
        $this->appendClass($this->uses, $className, $alias, 'object');

        return $this;
    }

    /**
     * Add Interface to be implemented.
     *
     * @param  string       $interfaceName
     * @param  string|null  $alias
     */
    public function addInterface($interfaceName, $alias = null)
    {
        // $this->interfaces[$interfaceName] = $alias?: basename($interfaceName);
        $this->appendClass($this->interfaces, $interfaceName, $alias, 'interface');

        return $this;
    }

    /**
     * Add Trait.
     *
     * @param  string       $traitName
     * @param  string|null  $alias
     */
    public function addTrait($traitName, $alias = null)
    {
//        if ( array_search($traitName, get_declared_traits()) === false ) {
//            //dump(get_declared_traits());
//            throw new InvalidArgumentException("$traitName is not a valid trait");
//        }

        //$this->traits[$traitName] = $alias?: basename($traitName);
        $this->appendClass($this->traits, $traitName, $alias, 'trait');

        return $this;
    }

    /**
     * Add Class to class collection.
     *
     * @param  array        $class
     * @param  string       $name
     * @param  string|null  $alias
     * @param  string       $type
     */
    protected function appendClass(&$array, $name, $alias = null, $type = 'class')
    {
        if (strstr($name, ' as ')) {
            $name = explode(' as ', $name);
            $alias = trim($name[1]);
            $name = trim($name[0]);
        } elseif ($alias === null) {
            $alias = class_basename($name);
        }

        if (!isset($array[$name])) {
            $array[$name] = $alias;
        } elseif ($array[$name] !== $alias) {
            throw new SkeletonUseAliasException($alias, $name, $array[$name]);
        }

        return $this;
    }

    /**
     * Add phpDoc Tag.
     *
     * @param  \Triun\ModelBase\Definitions\PhpDocTag $value
     */
    public function addPhpDocTag(PhpDocTag $value)
    {
        $this->phpDocTags[$value->getName()] = $value;

        return $this;
    }

    /**
     * Set Constant.
     *
     * @param  \Triun\ModelBase\Definitions\Constant $value
     */
    public function addConstant(Constant $value)
    {
        $this->constants[$value->name] = $value;

        return $this;
    }

    /**
     * Set Property.
     *
     * @param  \Triun\ModelBase\Definitions\Property $value
     */
    public function addProperty(Property $value)
    {
        $this->properties[$value->name] = $value;

        return $this;
    }

    /**
     * Set Method.
     *
     * @param  \Triun\ModelBase\Definitions\Method $value
     */
    public function addMethod(Method $value)
    {
        $this->methods[$value->name] = $value;

        return $this;
    }

    /**
     * Unset Constant.
     *
     * @param  string  $key
     */
    public function removeConstant($key)
    {
        unset($this->constants[$key]);

        return $this;
    }

    /**
     * Unset Property.
     *
     * @param  string  $key
     */
    public function removeProperty($key)
    {
        unset($this->properties[$key]);

        return $this;
    }

    /**
     * Unset Method.
     *
     * @param  string  $key
     */
    public function removeMethod($key)
    {
        unset($this->methods[$key]);

        return $this;
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
