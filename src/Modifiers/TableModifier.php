<?php

namespace Triun\ModelBase\Modifiers;

use Triun\ModelBase\Lib\ModifierBase;
use Triun\ModelBase\Definitions\Skeleton;

class TableModifier extends ModifierBase
{
    /**
     * Apply the modifications of the class.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     */
    public function apply(Skeleton $skeleton)
    {
        $this->setTableName($skeleton);
        $this->setPrimaryKey($skeleton);
    }

    protected function setTableName(Skeleton $skeleton)
    {
        // TODO TEST: See how to get out laravel prefix. ($this->_conn->getTablePrefix())
        $skeleton->property('table')->setValue($this->table()->getName());
    }

    /**
     * @param  Skeleton $skeleton
     */
    protected function setPrimaryKey($skeleton)
    {
        $name = null;
        $type = null;
        $incrementing = false;

        // TODO TEST: Check if it return one or more fields in a composed primary key.
        // TODO TEST: Check primary keys types other than null or int.
        if ($this->table()->hasPrimaryKey()) {
            $name = $this->table()->getPrimaryKey()->getColumns()[0];
            $column = $this->table()->getColumn($name);
            $type = $column->getType()->getName();
            $incrementing = $column->getAutoincrement();
        }

        // integer is int
        if ($type === 'integer') {
            $type = 'int';
        }

        $skeleton->property('primaryKey')->setValue($name);
        $skeleton->property('keyType')->setValue($type);
        $skeleton->property('incrementing')->setValue($incrementing);
    }
}
