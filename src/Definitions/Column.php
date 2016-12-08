<?php


namespace Triun\ModelBase\Definitions;


use Doctrine\DBAL\Schema\Column as DoctrineColumn;

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
     * @var string Laravel casting type of this column. Possible PHP types include:
     * integer, real, float, double, string, boolean, object, array, collection, date, datetime, and timestamp.
     */
    public $laravelType;

    /**
     * Whether if the column is a date or not.
     *
     * @var bool
     */
    public $isDate;

    /**
     * @var string Laravel php type returned by Eloquent.
     */
    public $castType;

    /**
     * @var string Laravel casting type for Eloquent.
     */
    public $phpDocType;

    /**
     * @var string the DB type of this column. Possible DB types vary according to the type of DBMS.
     */
    public $dbType;
}