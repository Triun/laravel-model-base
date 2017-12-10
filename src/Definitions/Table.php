<?php

namespace Triun\ModelBase\Definitions;

use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table as DoctrineTable;

/**
 * Class Table
 *
 * @package Triun\ModelBase\Definitions
 */
class Table extends DoctrineTable
{
    /**
     * @return \Triun\ModelBase\Definitions\Column[]
     */
    public function getColumns()
    {
        return parent::getColumns();
    }

    /**
     * Returns the Column with the given name.
     *
     * @param string $columnName The column name.
     *
     * @return \Triun\ModelBase\Definitions\Column
     *
     * @throws SchemaException If the column does not exist.
     */
    public function getColumn($columnName)
    {
        return parent::getColumn($columnName);
    }
}
