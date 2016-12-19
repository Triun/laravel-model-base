<?php

namespace Triun\ModelBase\Modifiers;

use ReflectionClass;
use Illuminate\Database\Query\Builder;
use Triun\ModelBase\Lib\ModifierBase;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Definitions\PhpDocTag;

class PhpDocModifier extends ModifierBase
{
    /**
     * Apply the modifications of the class.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     */
    public function apply(Skeleton $skeleton)
    {
        foreach ($this->table()->getColumns() as $column) {
            $skeleton->addPhpDocTag(new PhpDocTag(
                '$'.$column->snakeName,
                'property',
                $column->phpDocType,
                str_pad($column->dbType, 11).' '.$column->getComment()
            ));

            // Avoid existent methods as whereDate or WhereColumn
            $method = "where{$column->studName}";
            if (!$skeleton->hasMethod($method) && !$this->hasMethod($method, Builder::class)) {
                $skeleton->addPhpDocTag(new PhpDocTag(
                    "{$method}(\$value)",
                    'method',
                    'static \\Illuminate\\Database\\Query\\Builder|\DummyNamespace\DummyClass',
                    $column->getComment()
                ));
            }
        }
    }

    public function hasMethod($method, $className)
    {
        $reflectionClass = new ReflectionClass($className);

        return $reflectionClass->hasMethod($method);
    }
}
