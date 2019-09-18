<?php

namespace Triun\ModelBase\Utils;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Triun\ModelBase\Util;
use Triun\ModelBase\Lib\BuilderUtilBase;
use Triun\ModelBase\Definitions\Constant;
use Triun\ModelBase\Definitions\Method;
use Triun\ModelBase\Definitions\Property;
use Triun\ModelBase\Definitions\Skeleton;

/**
 * Class BuilderUtil
 *
 * @package Triun\ModelBase\Utils
 */
class BuilderUtil extends BuilderUtilBase
{
    /**
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     * @param bool|string                           $override
     * @param string                                $path
     * @param string                                $stub
     *
     * @return int The method returns the number of bytes that were written to the file, or false on failure.
     * @throws \Exception
     */
    public function build(Skeleton $skeleton, $override = Util::CONFIRM, &$path = null, $stub = 'class.stub')
    {
        $path = $this->getSkeletonPath($skeleton);

        $content = $this->getContents($skeleton, $stub);

        return $this->save($path, $skeleton->className, $content, $override, $skeleton);
    }

    /**
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     * @param string                                $stub
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getContents(Skeleton $skeleton, $stub)
    {
        $content = File::get($this->getStub($stub));

        $replace = [
            //'dummy_cmd' => $this->name,

            '{{phpdoc}}' => implode(PHP_EOL, $this->getPHPDoc($skeleton)),

            'DummyNamespace'     => $skeleton->getNamespace(),
            'DummyRootNamespace' => App::getNamespace(),
            'DummyClass'         => class_basename($skeleton->className),

            'DummyExtendsNamespace' => $skeleton->extends,
            'DummyExtendsClass'     => class_basename($skeleton->extends),

            PHP_EOL . PHP_EOL . '{{uses}}' => $this->getUses($skeleton),

            '{{implements}}' => $this->getImplements($skeleton),

            PHP_EOL . '{{body}}' => $this->getBody($skeleton),
        ];

        return str_replace(array_keys($replace), array_values($replace), $content);
    }

    /**
     * Get the destination class path.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     *
     * @return string
     * @throws \Exception
     */
    public function getSkeletonPath(Skeleton $skeleton): string
    {
        return $this->getClassNamePath($skeleton->className);
    }

    /**
     * Get all the use in the header, before declaring the object.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     *
     * @return string
     */
    protected function getUses(Skeleton $skeleton): string
    {
        $parts = array_map(function ($value) {
            return 'use ' . ltrim($value, '\\') . ';';
        }, $skeleton->uses());

        if (count($parts) === 0) {
            return '';
        }

        return PHP_EOL . PHP_EOL . implode(PHP_EOL, $parts);
    }

    /**
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     *
     * @return string
     */
    protected function getImplements(Skeleton $skeleton): string
    {
        if (count($skeleton->interfaces()) > 0) {
            return ' implements ' . implode(', ', $skeleton->interfaces());
        }

        return '';
    }

    /**
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     *
     * @return string
     */
    protected function getBody(Skeleton $skeleton): string
    {
        $traits = $this->getTraits($skeleton->traits());

        $parts = array_merge(
            '' === $traits ? [] : [$traits],
            array_map([$this, 'formatConstant'], $skeleton->dirtyConstants()),
            array_map([$this, 'formatProperty'], $skeleton->dirtyProperties()),
            array_map([$this, 'formatMethod'], $skeleton->dirtyMethods())
        );

        if (count($parts) === 0) {
            return '';
        }

        return PHP_EOL . implode(PHP_EOL . PHP_EOL, $parts);
    }

    /**
     * @param string[] $traits
     *
     * @return string
     */
    protected function getTraits($traits): string
    {
        if (count($traits) === 0) {
            return '';
        }

        return static::TAB . 'use ' . implode(', ', $traits) . ';';
    }

    /**
     * @param Constant $constant
     *
     * @return string
     */
    protected function formatConstant(Constant $constant): string
    {
        // TODO: Add phpDoc
        return static::TAB . 'const ' . $constant->name . ' = ' . var_export54($constant->value, true) . ';';
//        return static::TAB.'const '.$constant->name.' = '.$this->value2File($constant->value).';';
    }

    /**
     * @param Property $property
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function formatProperty(Property $property)
    {
        $content = File::get($this->getStub('property.stub'));

        $replace = [
            '// DummyPhpDoc'  => $property->docComment,
            'DummyDefinition' => implode(' ', $property->modifiers),
            'DummyName'       => '$' . $property->name,
            'DummyValue'      => var_export54($property->value),
            //            'DummyValue'        => $this->value2File($property->value),
        ];

        return str_replace(array_keys($replace), array_values($replace), $content);
    }

    /**
     * @param Method $method
     *
     * @return string
     */
    protected function formatMethod(Method $method)
    {
        return $method->value;
    }
}

if (!function_exists('var_export54')) {
    /**
     * @param mixed  $value
     * @param int    $tabs
     * @param bool   $tabulateKeys
     * @param string $TAB
     *
     * @return string
     */
    function var_export54($value, $tabs = 1, $tabulateKeys = true, $TAB = '    ')
    {
        switch (gettype($value)) {
            case 'array':
                if (count($value) === 0) {
                    return '[]';
                }

                $indent = str_repeat($TAB, $tabs);
                $assoc  = count(array_diff(array_keys($value), array_keys(array_keys($value)))) > 0;

                // Tabular keys
                $pad_length = 0;
                if ($assoc) {
                    foreach (array_keys($value) as $key) {
                        $pad_length = max($pad_length, strlen($key) + 2);
                    }
                }

                $export = [];
                foreach ($value as $key => $subValue) {
                    $export[] = $indent . $TAB
                                . ($assoc ? str_pad(var_export54($key), $pad_length) . ' => ' : '')
                                . var_export54($subValue, $tabs + 2, $tabulateKeys);
                }

                return '[' . PHP_EOL . implode(',' . PHP_EOL, $export) . ',' . PHP_EOL . $indent . ']';

            // Null as lowercase to pass PSR-2 standard
            case 'NULL':
                return 'null';

            case 'string':
                //return '"' . addcslashes($value, "\\\$\"\r\n\t\v\f") . '"';
                //break;
            case 'boolean':
            default:
                return var_export($value, true);
        }
    }
}
