<?php


namespace Triun\ModelBase\Definitions;


use Doctrine\DBAL\Schema\Table as DoctrineTable;

class Table extends DoctrineTable
{
    /**
     * @var Column[]
     */
    protected $_columns = [];

    /**
     * @return \Triun\ModelBase\Definitions\Column[]
     */
    public function getColumns()
    {
        return parent::getColumns(); // TODO: Change the autogenerated stub
    }
}