<?php

namespace Triun\ModelBase\Modifiers;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Triun\ModelBase\AddOns\MutatorSkipeable;
use Triun\ModelBase\Definitions\Column;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Lib\ModifierBase;

class ColumnAliasModifier extends ModifierBase
{
    protected string $getAttributeMethod_stub = 'getter-setter-attributes/getAttributeMethod.stub';
    protected string $setAttributeMethod_stub = 'getter-setter-attributes/setAttributeMethod.stub';
    protected bool $trait_added = false;

    /**
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function apply(Skeleton $skeleton): void
    {
        foreach ($this->table()->getColumns() as $column) {
            // It may get the namespace... in that case, use $column->toArray()['name'] instead.
            if ($column->aliasSnakeName !== null && $column->aliasSnakeName !== $column->snakeName) {
                $this->addMutators($skeleton, $column);
            }
        }
    }

    /**
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function addMutators(Skeleton $skeleton, Column $column): void
    {
        $name   = $column->getName();
        $snake  = $column->aliasSnakeName;

        if ($name !== $snake) {
            $stud   = $column->aliasStudName;
            $phpDoc = $column->phpDocType;

            $skeleton->addMethod($this->util()->makeMethod('get'.$stud.'Attribute', $this->getAttributeMethod(), [
                'DummyNamespace'    => $skeleton->getNamespace(),
                'DummyClass'        => $skeleton->getClassBasename(),
                'DummyDescription'  => "Alias getter: $name -> $snake.",
                'dummyType'         => $phpDoc,
                'DummyName'         => $stud,
                'dummy_name'        => $name,
                'dummy_snake_name'  => $snake,
            ]));

            $skeleton->addMethod($this->util()->makeMethod('set'.$stud.'Attribute', $this->setAttributeMethod(), [
                'DummyNamespace'    => $skeleton->getNamespace(),
                'DummyClass'        => $skeleton->getClassBasename(),
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
     * @throws FileNotFoundException
     */
    protected function getAttributeMethod(): string
    {
        static $content;

        if ($content === null) {
            $content = $this->getFile($this->getStub($this->getAttributeMethod_stub));
        }

        return $content;
    }

    /**
     * @throws FileNotFoundException
     */
    protected function setAttributeMethod(): string
    {
        static $content;

        if ($content === null) {
            $content = $this->getFile($this->getStub($this->setAttributeMethod_stub));
        }

        return $content;
    }
}
