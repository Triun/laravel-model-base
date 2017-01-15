<?php

namespace Triun\ModelBase\Definitions;

use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table as DoctrineTable;

class Table extends DoctrineTable
{
    /**
     * @return Column[]
     */
    public function getColumns()
    {
        parent::getColumns();
    }

    /**
     * Returns the Column with the given name.
     *
     * @param string $columnName The column name.
     *
     * @return Column
     *
     * @throws SchemaException If the column does not exist.
     */
    public function getColumn($columnName)
    {
        parent::getColumn($columnName);
    }
}
