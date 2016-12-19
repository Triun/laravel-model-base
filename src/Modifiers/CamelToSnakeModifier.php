<?php

namespace Triun\ModelBase\Modifiers;

use Triun\ModelBase\MutatorSkipeable;
use Triun\ModelBase\Lib\ModifierBase;
use Triun\ModelBase\Definitions\Column;
use Triun\ModelBase\Definitions\Skeleton;

class CamelToSnakeModifier extends ModifierBase
{
    protected $getAttributeMethod_stub = 'camel-to-snake/getAttributeMethod.stub';
    protected $setAttributeMethod_stub = 'camel-to-snake/setAttributeMethod.stub';

    protected $trait_added = false;

    /**
     * Apply the modifications of the class.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     */
    public function apply(Skeleton $skeleton)
    {
        // Only if snakeAttributes is true.
        if (!$skeleton->property('snakeAttributes')->value) {
            return;
        }

        foreach ($this->table()->getColumns() as $column) {
            $name = $column->getName();
            // It may get the namespace... in that case, use $column->toArray()['name'] instead.
            if ($name !== strtolower($name)) {
                $this->addSnakeMuttators($skeleton, $column);
            }
        }
    }

    /**
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     * @param \Triun\ModelBase\Definitions\Column  $column
     */
    public function addSnakeMuttators(Skeleton $skeleton, Column $column)
    {
        $name   = $column->getName();
        $snake  = $column->snakeName;

        if ($name !== $snake) {
            $stud   = $column->studName;
            $phpDoc = $column->phpDocType;

            $skeleton->addMethod($this->util()->makeMethod('get'.$stud.'Attribute', $this->getAttributeMethod(), [
                'DummyDescription'  => "Snake name getter: $name -> $snake.",
                'dummyType'         => $phpDoc,
                'DummyName'         => $stud,
                'dummy_name'        => $name,
            ]));

            $skeleton->addMethod($this->util()->makeMethod('set'.$stud.'Attribute', $this->setAttributeMethod(), [
                'DummyDescription'  => "Snake name setter: $name -> $snake.",
                'dummyType'         => $phpDoc,
                'DummyName'         => $stud,
                'dummy_name'        => $name,
            ]));

            if (!$this->trait_added) {
                $skeleton->addTrait(MutatorSkipeable::class);
            }
        }
    }

    /**
     * @return string
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
