<?php

namespace Triun\ModelBase\Definitions;

use Doctrine\DBAL\Schema\Column as DoctrineColumn;

/**
 * Class Column
 *
 * @package Triun\ModelBase\Definitions
 */
class Column extends DoctrineColumn
{
    /**
     * @var string
     */
    public $publicName;

    /**
     * @var string
     */
    public $snakeName;

    /**
     * @var string
     */
    public $studName;

    /**
     * @var string
     */
    public $alias;

    /**
     * @var string
     */
    public $aliasSnakeName;

    /**
     * @var string
     */
    public $aliasStudName;

    /**
     * The DB type of this column. Possible DB types vary according to the type of DBMS.
     *
     * @var string
     */
    public $dbType;

    /**
     * phpDoc property type.
     *
     * @var string
     */
    public $phpDocType;

    /**
     * Laravel casting type of this column. Possible PHP types include:
     * integer, real, float, double, string, boolean, object, array, collection, date, datetime, and timestamp.
     *
     * @var string
     */
    public $laravelType;

    /**
     * Laravel php type returned by Eloquent.
     *
     * @var string
     */
    public $castType;

    /**
     * Whether if the column is a date or not.
     *
     * @var bool
     */
    public $isDate;

    /**
     * Whether if the column is unsigned or not.
     *
     * @var bool
     */
    public $unsigned;

    /**
     * Whether if the column is nullable or not.
     *
     * @var bool
     */
    public $nullable;
}
