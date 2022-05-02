<?php

namespace Triun\ModelBase\Definitions;

use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table as DoctrineTable;

/**
 * Notice: this is used so that we can work in our Colum class instead of the DBAL one.
 */
class Table extends DoctrineTable
{
    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return parent::getColumns();
    }

    /**
     * Returns the Column with the given name.
     *
     * @param string $name The column name.
     *
     * @return Column
     *
     * @throws SchemaException If the column does not exist.
     */
    public function getColumn($name): Column
    {
        return parent::getColumn($name);
    }
}
