<?php

namespace Triun\ModelBase\Utils;

use Exception;
use Throwable;
use Triun\ModelBase\Definitions\Constant;
use Triun\ModelBase\Definitions\Method;
use Triun\ModelBase\Definitions\Property;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Lib\BuilderUtilBase;
use Triun\ModelBase\Util;

class BuilderUtil extends BuilderUtilBase
{
    /**
     * @return int The method returns the number of bytes that were written to the file, or false on failure.
     * @throws Throwable
     */
    public function build(
        Skeleton $skeleton,
        bool|string $override = Util::CONFIRM,
        ?string &$path = null,
        string $stub = 'class.stub',
    ): int {
        $path = $this->getSkeletonPath($skeleton);

        $content = $this->getContents($skeleton, $stub);

        return $this->save($path, $skeleton->className, $content, $override, $skeleton);
    }

    protected function getContents(Skeleton $skeleton, string $stub): string
    {
        $content = file_get_contents($this->getStub($stub));

        $replace = [
            //'dummy_cmd' => $this->name,

            '{{phpdoc}}' => implode(PHP_EOL, $this->getPHPDoc($skeleton)),

            'DummyNamespace'     => $skeleton->getNamespace(),
            'DummyRootNamespace' => app()->getNamespace(),
            'DummyClass'         => $skeleton->getClassBasename(),

            'DummyExtendsNamespace' => $skeleton->extends(),
            'DummyExtendsClass'     => $skeleton->extendsAlias(),

            '{{abstract}}'   => $skeleton->isAbstract ? 'abstract ' : '',
            '{{uses}}'       => implode(PHP_EOL, $this->getUses($skeleton)),
            '{{implements}}' => $this->getImplements($skeleton),
            '{{body}}'       => $this->getBody($skeleton),
        ];

        return str_replace(array_keys($replace), array_values($replace), $content);
    }

    /**
     * Get the destination class path.
     *
     * @throws Exception
     */
    public function getSkeletonPath(Skeleton $skeleton): string
    {
        return $this->getClassNamePath($skeleton->className);
    }

    /**
     * Get all the use in the header, before declaring the object.
     *
     * @return string[]
     */
    protected function getUses(Skeleton $skeleton): array
    {
        $uses = [];
        foreach ($skeleton->uses() as $alias => $class) {
            $uses[] = 'use ' . ltrim($class, '\\') .
                      (class_basename($class) === $alias ? '' : ' as ' . $alias) . ';';
        }

        return $uses;
    }

    protected function getImplements(Skeleton $skeleton): string
    {
        if (count($skeleton->interfaces()) > 0) {
            return ' implements ' . implode(', ', $skeleton->interfaces());
        }

        return '';
    }

    protected function getBody(Skeleton $skeleton): string
    {
        $traits = $this->getTraits($skeleton->traits());

        $parts = array_merge(
            null === $traits ? [] : [$traits],
            array_map([$this, 'formatConstant'], $skeleton->dirtyConstants()),
            array_map([$this, 'formatProperty'], $skeleton->dirtyProperties()),
            array_map([$this, 'formatMethod'], $skeleton->dirtyMethods()),
        );

        if (count($parts) === 0) {
            return PHP_EOL;
        }

        return PHP_EOL . implode(PHP_EOL . PHP_EOL, $parts) . PHP_EOL;
    }

    /**
     * @param string[] $traits
     */
    protected function getTraits(array $traits): ?string
    {
        if (count($traits) === 0) {
            return null;
        }

        return static::TAB . 'use ' . implode(', ', $traits) . ';';
    }

    protected function formatConstant(Constant $constant): string
    {
        // TODO: Add phpDoc
        return static::TAB . 'const ' . $constant->name . ' = ' . var_export54($constant->value, true) . ';';
//        return static::TAB.'const '.$constant->name.' = '.$this->value2File($constant->value).';';
    }

    protected function formatProperty(Property $property): string|array
    {
        $content = file_get_contents($this->getStub('property.stub'));

        $replace = [
            '// DummyPhpDoc'  => $property->docComment,
            'DummyDefinition' => implode(' ', $property->modifiers),
            'DummyName'       => '$' . $property->name,
            'DummyValue'      => var_export54($property->value),
            //'DummyValue'        => $this->value2File($property->value),
        ];

        return str_replace(array_keys($replace), array_values($replace), $content);
    }

    protected function formatMethod(Method $method): string
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
    function var_export54(mixed $value, int $tabs = 1, bool $tabulateKeys = true, string $TAB = '    '): string
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
