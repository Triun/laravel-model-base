<?php

namespace Triun\ModelBase\Utils;

use Doctrine\DBAL\Schema\Table;
use Exception;
use Illuminate\Support\Str;
use Reflection;
use ReflectionClass;
use ReflectionProperty;
use Triun\ModelBase\Definitions\Constant;
use Triun\ModelBase\Definitions\Method;
use Triun\ModelBase\Definitions\Property;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Lib\ConnectionUtilBase;
use Triun\ModelBase\Lib\ModifierBase;

class SkeletonUtil extends ConnectionUtilBase
{
    /**
     * Generate skeleton from table
     *
     * @param \Doctrine\DBAL\Schema\Table                       $table
     * @param string|null                                       $className
     * @param \Triun\ModelBase\Definitions\Skeleton|string|null $extends
     * @param string[]                                          $modifiers
     * @param bool                                              $isAbstract
     *
     * @return Skeleton
     * @throws Exception
     */
    public function make(
        Table $table,
        ?string $className = null,
        Skeleton|string|null $extends = null,
        array $modifiers = [],
        bool $isAbstract = false
    ): Skeleton {
        // New empty Skeleton
        $skeleton = new Skeleton;

        $skeleton->isAbstract = $isAbstract;
        $skeleton->className  = self::parseName($className);

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

    public function makeConstant(string $name): Constant
    {
        $item = new Constant;

        $item->name = $name;

        return $item;
    }

    public function makeProperty(
        string $name,
        int $modifiers_id = ReflectionProperty::IS_PUBLIC,
        ?string $docComment = null
    ): Property {
        $item = new Property;

        $item->name         = $name;
        $item->modifiers_id = $modifiers_id;
        $item->modifiers    = Reflection::getModifierNames($modifiers_id);
        $item->docComment   = $docComment;

        return $item;
    }

    public function makeMethod(string $name, ?string $stub = null, array $replace = []): Method
    {
        $item = new Method;

        $item->name = $name;

        if (null !== $stub) {
            $item->value = str_replace(array_keys($replace), array_values($replace), $stub);
        }

        return $item;
    }

    public function setConstant(Skeleton $skeleton, string $name, mixed $value): static
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

    public function setProperty(
        Skeleton $skeleton,
        string $name,
        mixed $value,
        int $modifiers_id = ReflectionProperty::IS_PUBLIC,
        ?string $docComment = null
    ): static {
        // If it is already a Property, just save it.
        if ($value instanceof Property) {
            $skeleton->addProperty($value);

            return $this;
        }

        // If doesn't exists, create a new one.
        if (!$skeleton->hasProperty($name)) {
            $skeleton->addProperty($this->makeProperty($name, $modifiers_id, $docComment));
        }

        // Save the value.
        $skeleton->property($name)->setValue($value);

        return $this;
    }

    public function setMethod(Skeleton $skeleton, string $name, string|Method $value): static
    {
        // If it is already a Method, just save it.
        if ($value instanceof Method) {
            $value = $this->makeMethod($name);
        }

        // If it doesn't exist, create a new one.
        if (!$skeleton->hasMethod($name)) {
            $skeleton->addMethod($value);
        }

        // Save the value.
        //$skeleton->method($name)->setValue($value);

        return $this;
    }

    /**
     * Make a default skeleton from a class given.
     *
     * @throws Exception
     */
    public static function extend(Skeleton $skeleton, Skeleton|string $extendClassName, bool $overwrite = false): void
    {
        if ($skeleton->extends !== null && !$overwrite) {
            throw new Exception("The skeleton {$skeleton->className} already extends {$skeleton->extends}");
        }

        if ($extendClassName instanceof Skeleton) {
            $extendClassName = $extendClassName->className;
        }

        if (!class_exists($extendClassName)) {
            throw new Exception("The class $extendClassName doesn't exists");
        }

        // Save what are we extending it from
        $skeleton->extends = self::parseName($extendClassName);

        /*if ($reflectionClass->isSubclassOf('Illuminate\Database\Eloquent\Model')) {
            //
        }*/

        static::loadReflection($skeleton, $extendClassName);
    }

    public static function loadReflection(Skeleton $skeleton, string $className): void
    {
        // Generate reflextion class...
        try {
            $reflectionClass = new ReflectionClass($className);
        } catch (\ReflectionException $exception) {
            throw new \RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
        }

        // Constants
        foreach ($reflectionClass->getConstants() as $name => $value) {
            $item             = new Constant();
            $item->name       = $name;
            $item->docComment = '';
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
                $modifiers          = $reflectionProperty->getModifiers();
                $item               = new Property();
                $item->name         = $reflectionProperty->getName();
                $item->modifiers_id = $modifiers;
                $item->modifiers    = Reflection::getModifierNames($modifiers);
                $item->docComment   = $reflectionProperty->getDocComment();
                $item->default      = $item->value = $defaults[$item->name]; //$reflectionProperty->getValue();

                $skeleton->addProperty($item);
            }
        }

        // Methods
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            // Do not add private properties
            if (!$reflectionMethod->isPrivate()) {
                $modifiers          = $reflectionMethod->getModifiers();
                $item               = new Method();
                $item->file         = $reflectionMethod->getFileName();
                $item->line         = [$reflectionMethod->getStartLine(), $reflectionMethod->getEndLine()];
                $item->name         = $reflectionMethod->getName();
                $item->modifiers_id = $modifiers;
                $item->modifiers    = Reflection::getModifierNames($modifiers);
                $item->docComment   = $reflectionMethod->getDocComment();
                //$method->default = $item->value = (string) $reflectionMethod;

                $skeleton->addMethod($item);
            }
        }
    }

    public static function appendToMethod(Method $method, string $code): void
    {
        if ($method->value === null) {
            static::loadMethodValue($method);
        }

        // Format code with padding...
        $lines = explode(PHP_EOL, $code);

        if (!Str::startsWith($lines[0], '        ')) {
            $padding = 0;
            // Detect how much padding it has
            if (strlen($lines[0]) > 0) {
                while ($lines[0][$padding] === ' ') {
                    $padding++;
                }
            }
            $padding = 8 - $padding;
            // Add the left padding
            for ($i = 0; $i < count($lines); $i++) {
                $lines[$i] = str_repeat(' ', $padding) . $lines[$i];
            }

            $code = implode(PHP_EOL, $lines);
        }

        // Append to value
        $closePos      = strrpos($method->value, '}');
        $lastLine      = strrpos(substr($method->value, 0, $closePos), "\n");
        $method->value = substr($method->value, 0, $lastLine) . PHP_EOL . $code . substr($method->value, $lastLine);
    }

    public static function loadMethodValue(Method $method): void
    {
        // The \ReflectionMethod class doesn't return the content of the function, so we have to get it from
        // the original file.

        $fileContent = static::getFileContent(
            $method->getFileName(),
            $method->getStartLine() - 1,
            $method->getEndLine()
        );

        $method->default = $method->value = '    '
                                            . $method->getDocComment() . PHP_EOL
                                            . $fileContent;
    }

    public static function getFileContent(string $file, int $startLine, int $endLine): string
    {
        static $cached = [];

        $file = realpath($file);

        if (!isset($cached[$file])) {
            $cached[$file] = file($file);
        }

        return implode('', array_slice($cached[$file], $startLine, $endLine - $startLine));
    }

    /**
     * Parse the name and format according to the root namespace.
     */
    public static function parseName(string $name): string
    {
        if (Str::contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        return trim($name, '\\');
    }
}
