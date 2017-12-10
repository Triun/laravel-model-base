<?php

namespace Triun\ModelBase\Modifiers;

use Triun\ModelBase\Lib\ModifierBase;
use Triun\ModelBase\Definitions\Column;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\AddOns\MutatorSkipeable;

class ColumnAliasModifier extends ModifierBase
{
    /**
     * @var string
     */
    protected $getAttributeMethod_stub = 'getter-setter-attributes/getAttributeMethod.stub';

    /**
     * @var string
     */
    protected $setAttributeMethod_stub = 'getter-setter-attributes/setAttributeMethod.stub';

    /**
     * @var boolean
     */
    protected $trait_added = false;

    /**
     * Apply the modifications of the class.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    public function apply(Skeleton $skeleton)
    {
        foreach ($this->table()->getColumns() as $column) {
            // It may get the namespace... in that case, use $column->toArray()['name'] instead.
            if ($column->aliasSnakeName !== null && $column->aliasSnakeName !== $column->snakeName) {
                $this->addMuttators($skeleton, $column);
            }
        }
    }

    /**
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     * @param \Triun\ModelBase\Definitions\Column   $column
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    public function addMuttators(Skeleton $skeleton, Column $column)
    {
        $name   = $column->getName();
        $snake  = $column->aliasSnakeName;

        if ($name !== $snake) {
            $stud   = $column->aliasStudName;
            $phpDoc = $column->phpDocType;

            $skeleton->addMethod($this->util()->makeMethod('get'.$stud.'Attribute', $this->getAttributeMethod(), [
                'DummyNamespace'    => $skeleton->getNamespace(),
                'DummyClass'        => class_basename($skeleton->className),
                'DummyDescription'  => "Alias getter: $name -> $snake.",
                'dummyType'         => $phpDoc,
                'DummyName'         => $stud,
                'dummy_name'        => $name,
                'dummy_snake_name'  => $snake,
            ]));

            $skeleton->addMethod($this->util()->makeMethod('set'.$stud.'Attribute', $this->setAttributeMethod(), [
                'DummyNamespace'    => $skeleton->getNamespace(),
                'DummyClass'        => class_basename($skeleton->className),
                'DummyDescription'  => "Alias setter: $name -> $snake.",
                'dummyType'         => $phpDoc,
                'DummyName'         => $stud,
                'dummy_name'        => $name,
                'dummy_snake_name'  => $snake,
            ]));

            if (!$this->trait_added) {
                $skeleton->addTrait(
                    $this->getAddOn(MutatorSkipeable::class)
                );
            }
        }
    }

    /**
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getAttributeMethod()
    {
        static $content;

        if ($content === null) {
            $content = $this->getFile($this->getStub($this->getAttributeMethod_stub));
        }

        return $content;
    }

    /**
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function setAttributeMethod()
    {
        static $content;

        if ($content === null) {
            $content = $this->getFile($this->getStub($this->setAttributeMethod_stub));
        }

        return $content;
    }
}
