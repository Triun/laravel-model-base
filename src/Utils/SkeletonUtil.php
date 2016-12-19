<?php

namespace Triun\ModelBase\Utils;

use Exception;
use Reflection;
use ReflectionClass;
use ReflectionProperty;
use Illuminate\Support\Str;
use Doctrine\DBAL\Schema\Table;
use Triun\ModelBase\Definitions\Method;
use Triun\ModelBase\Definitions\Property;
use Triun\ModelBase\Definitions\Constant;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Lib\ModifierBase;
use Triun\ModelBase\Lib\ConnectionUtilBase;

class SkeletonUtil extends ConnectionUtilBase
{
    /**
     * Generate skeleton from table
     *
     * @param \Doctrine\DBAL\Schema\Table                  $table
     * @param string                                       $className
     * @param string|\Triun\ModelBase\Definitions\Skeleton $extends
     * @param string[]                                     $modifiers
     *
     * @return Skeleton
     */
    public function make(Table $table, $className = null, $extends = null, $modifiers = [])
    {
        // New empty Skeleton
        $skeleton = new Skeleton;

        $skeleton->className = self::parseName($className);

        if ($extends !== null) {
            $this->extend($skeleton, $extends);
        }

        foreach ($modifiers as $modClass) {
            /* @var $mod ModifierBase */
            $mod = new $modClass($this, $table);
            $mod->apply($skeleton);
        }

        return $skeleton;
    }

    /**
     * @param string $name
     *
     * @return \Triun\ModelBase\Definitions\Constant
     */
    public function makeConstant($name)
    {
        $item = new Constant;

        $item->name = $name;

        return $item;
    }

    /**
     * @param string $name
     * @param integer $modifiers_id
     *
     * @return \Triun\ModelBase\Definitions\Property
     */
    public function makeProperty($name, $modifiers_id = ReflectionProperty::IS_PUBLIC)
    {
        $item = new Property;

        $item->name = $name;
        $item->modifiers_id = $modifiers_id;
        $item->modifiers = Reflection::getModifierNames($modifiers_id);

        return $item;
    }

    /**
     * @param string $name
     *
     * @return \Triun\ModelBase\Definitions\Method
     */
    public function makeMethod($name, $stub = null, $replace = [])
    {
        $item = new Method;

        $item->name = $name;

        if ($stub !== null) {
            $item->value = str_replace(array_keys($replace), array_values($replace), $stub);
        }

        return $item;
    }

    /**
     * Set constant value.
     *
     * @param  \Triun\ModelBase\Definitions\Skeleton $skeleton
     * @param  string $name
     * @param  mixed  $value
     *
     * @return $this
     */
    public function setConstant($skeleton, $name, $value)
    {
        // If it is already a Constant, just save it.
        if ($value instanceof Constant) {
            $skeleton->addConstant($value);

            return $this;
        }

        // If doesn't exists, create a new one.
        if (!$skeleton->hasConstant($name)) {
            $skeleton->addConstant($this->makeConstant($name));
        }

        // Save the value.
        $skeleton->constant($name)->setValue($value);

        return $this;
    }

    /**
     * Set property value.
     *
     * @param  \Triun\ModelBase\Definitions\Skeleton $skeleton
     * @param  string $name
     * @param  mixed  $value
     *
     * @return $this
     */
    public function setProperty($skeleton, $name, $value)
    {
        // If it is already a Property, just save it.
        if ($value instanceof Property) {
            $skeleton->addProperty($value);

            return $this;
        }

        // If doesn't exists, create a new one.
        if (!$skeleton->hasProperty($name)) {
            $skeleton->addProperty($this->makeProperty($name));
        }

        // Save the value.
        $skeleton->property($name)->setValue($value);

        return $this;
    }

    /**
     * Set method value.
     *
     * @param  \Triun\ModelBase\Definitions\Skeleton $skeleton
     * @param  string  $name
     * @param  mixed  $value
     *
     * @return $this
     */
    public function setMethod($skeleton, $name, $value)
    {
        // If it is already a Method, just save it.
        if ($value instanceof Method) {
            $skeleton->addMethod($value);

            return $this;
        }

        // If doesn't exists, create a new one.
        if (!$skeleton->hasMethod($name)) {
            $skeleton->addMethod($this->makeMethod($name));
        }

        // Save the value.
        //$skeleton->method($name)->setValue($value);

        return $this;
    }

    /**
     * Make a default skeleton from a class given.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     * @param string|\Triun\ModelBase\Definitions\Skeleton $extendClassName
     *
     * @throws Exception
     */
    public static function extend(Skeleton $skeleton, $extendClassName)
    {
        if ($extendClassName instanceof Skeleton) {
            $extendClassName = $extendClassName->className;
        }

        if (!class_exists($extendClassName)) {
            throw new Exception("The class $extendClassName doesn't exists");
        }

        $reflectionClass = new ReflectionClass($extendClassName);

        /*if ($reflectionClass->isSubclassOf('Illuminate\Database\Eloquent\Model')) {
            //
        }*/

        // Save what are we extending it from
        $skeleton->extends = self::parseName($extendClassName);

        // Constants
        foreach ($reflectionClass->getConstants() as $name => $value) {
            $item = new Constant();
            $item->name         = $name;
            $item->docComment   = '';
            // TODO: Add constants comments
            // (http://stackoverflow.com/questions/22103019/php-reflection-get-constants-doc-comment)
            $item->default = $item->value = $value;

            $skeleton->addConstant($item);
        }

        // Properties
        $defaults = $reflectionClass->getDefaultProperties();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            // Do not add private properties
            if (!$reflectionProperty->isPrivate()) {
                $modifiers = $reflectionProperty->getModifiers();
                $item = new Property();
                $item->name         = $reflectionProperty->getName();
                $item->modifiers_id = $modifiers;
                $item->modifiers    = Reflection::getModifierNames($modifiers);
                $item->docComment   = $reflectionProperty->getDocComment();
                $item->default = $item->value = $defaults[$item->name]; //$reflectionProperty->getValue();

                $skeleton->addProperty($item);
            }
        }

        // Methods
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            // Do not add private properties
            if (!$reflectionMethod->isPrivate()) {
                $modifiers = $reflectionMethod->getModifiers();
                $item = new Method();
                $item->name         = $reflectionMethod->getName();
                $item->modifiers_id = $modifiers;
                $item->modifiers    = Reflection::getModifierNames($modifiers);
                $item->docComment   = $reflectionMethod->getDocComment();
                $item->default = $item->value = (string) $reflectionMethod; //$reflectionProperty->getValue();

                $skeleton->addMethod($item);
            }
        }
    }

    /**
     * Parse the name and format according to the root namespace.
     *
     * @param  string  $name
     * @return string
     */
    public static function parseName($name)
    {
        if (Str::contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        return trim($name, '\\');
    }

    /**
     * Get the full namespace name for a given class.
     *
     * @param  string  $name
     * @return string
     */
//    protected function getNamespace($name)
//    {
//        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
//    }
}
