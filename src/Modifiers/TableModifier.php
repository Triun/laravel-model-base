<?php

namespace Triun\ModelBase\Modifiers;

use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Helpers\TypeHelper;
use Triun\ModelBase\Lib\ModifierBase;

/**
 * Class TableModifier
 *
 * @package Triun\ModelBase\Modifiers
 */
class TableModifier extends ModifierBase
{
    /**
     * Apply the modifications of the class.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     *
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function apply(Skeleton $skeleton)
    {
        $this->setTableName($skeleton);
        $this->setPrimaryKey($skeleton);
    }

    /**
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     */
    protected function setTableName(Skeleton $skeleton)
    {
        // TODO TEST: See how to get out laravel prefix. ($this->_conn->getTablePrefix())
        $skeleton->property('table')->setValue($this->table()->getName());
    }

    /**
     * @param Skeleton $skeleton
     *
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function setPrimaryKey($skeleton)
    {
        $name         = null;
        $type         = null;
        $incrementing = false;

        if ($this->table()->hasPrimaryKey()) {
            // TODO TEST: Check if it return one or more fields in a composed primary key.
            // TODO TEST: Check primary keys types other than null or int.
            // known issue with string, real, double, etc.
            // https://github.com/laravel/framework/issues/29824
            $name         = $this->table()->getPrimaryKey()->getColumns()[0];
            $column       = $this->table()->getColumn($name);
            $type         = TypeHelper::getLaravelType($column->getType());
            $incrementing = $column->getAutoincrement();

            // Laravel only allows int or string as primary key type
            // Big int require to be string, as PHP int is not big enough
            switch ($type) {
                case 'string':
                case 'int':
                    // Nothing to do
                    break;
                case 'integer':
                    $type = 'int';
                    break;
                case 'real':
                case 'float':
                case 'double':
                default:
                    $type = 'string';
            }
        }

        $skeleton->property('primaryKey')->setValue($name);
        $skeleton->property('keyType')->setValue($type);
        $skeleton->property('incrementing')->setValue($incrementing);
    }
}
