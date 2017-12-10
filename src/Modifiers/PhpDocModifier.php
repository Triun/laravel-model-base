<?php

namespace Triun\ModelBase\Modifiers;

use ReflectionClass;
use Illuminate\Database\Query\Builder;
use Triun\ModelBase\Lib\ModifierBase;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Definitions\PhpDocTag;

/**
 * Class PhpDocModifier
 *
 * @package Triun\ModelBase\Modifiers
 */
class PhpDocModifier extends ModifierBase
{
    /**
     * Apply the modifications of the class.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     */
    public function apply(Skeleton $skeleton)
    {
        $BuilderReflectionClass = new ReflectionClass(Builder::class);

        foreach ($this->table()->getColumns() as $column) {
            $skeleton->addPhpDocTag(new PhpDocTag(
                '$' . $column->snakeName,
                'property',
                $column->phpDocType,
                str_pad($column->dbType, 11) . ' ' . $column->getComment()
            ));

            // Avoid existent methods as whereDate or WhereColumn
            $method = "where{$column->studName}";
            if ($skeleton->hasMethod($method) || $BuilderReflectionClass->hasMethod($method)) {
                continue;
            }

            $skeleton->addPhpDocTag(new PhpDocTag(
                "{$method}(\$value)",
                'method',
                'static \\Illuminate\\Database\\Query\\Builder|\DummyNamespace\DummyClass',
                $column->getComment()
            ));
        }
    }
}
