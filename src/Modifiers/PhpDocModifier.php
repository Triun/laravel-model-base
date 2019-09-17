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
     * @var string[]
     */
    protected $defaultMixing = [
        '\\Illuminate\\Database\\Query\\Builder',
        '\\Illuminate\\Database\\Eloquent\\Builder',
    ];

    /**
     * Apply the modifications of the class.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     *
     * @throws \ReflectionException
     */
    public function apply(Skeleton $skeleton)
    {
        $BuilderReflectionClass = new ReflectionClass(Builder::class);

        $this->columnsPHPDoc($skeleton, $BuilderReflectionClass);

        $this->mixinPhpDoc($skeleton);
    }

    /**
     * Add properties tags.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     * @param \ReflectionClass                      $BuilderReflectionClass
     */
    protected function columnsPhpDoc(Skeleton $skeleton, ReflectionClass $BuilderReflectionClass)
    {
        foreach ($this->table()->getColumns() as $column) {
            $skeleton->addPhpDocTag(new PhpDocTag(
                '$' . $column->publicName,
                'property',
                $column->phpDocType,
                str_pad($column->dbType, 11) . ' ' . $column->getComment()
            ));
        }
    }

    /**
     * Add `mixin` phpDoc tags.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     */
    protected function mixinPhpDoc(Skeleton $skeleton)
    {
        $mixins = array_merge($this->config('mixin', []), $this->defaultMixing);

        foreach ($mixins as $mixin) {
            $skeleton->addPhpDocTag(new PhpDocTag(
                null,
                'mixin',
                $mixin
            ));
        }
    }
}
