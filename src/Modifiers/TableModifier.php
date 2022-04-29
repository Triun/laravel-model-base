<?php

namespace Triun\ModelBase\Modifiers;

use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Helpers\TypeHelper;
use Triun\ModelBase\Lib\ModifierBase;

class TableModifier extends ModifierBase
{
    /**
     * @throws SchemaException
     */
    public function apply(Skeleton $skeleton): void
    {
        $this->setTableName($skeleton);
        $this->setPrimaryKey($skeleton);
    }

    protected function setTableName(Skeleton $skeleton): void
    {
        // TODO TEST: See how to get out laravel prefix. ($this->_conn->getTablePrefix())
        $skeleton->property('table')->setValue($this->table()->getName());
    }

    /**
     * @throws SchemaException
     */
    protected function setPrimaryKey(Skeleton $skeleton): void
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
                    $type   = 'string';
                    $column = $this->table()->getColumn($name);
                    switch ($column->dbType) {
                        case Types::BIGINT:
                            $column->castType   = 'string';
                            $column->phpDocType = 'string|int';
                            break;
                        case Types::FLOAT:
                        case Types::DECIMAL:
                            $column->castType   = 'string';
                            $column->phpDocType = 'string|float';
                            break;
                    }
            }
        }

        $skeleton->property('primaryKey')->setValue($name);
        $skeleton->property('keyType')->setValue($type);
        $skeleton->property('incrementing')->setValue($incrementing);
    }
}
