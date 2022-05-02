<?php

namespace Triun\ModelBase\Definitions;

use Doctrine\DBAL\Schema\Column as DoctrineColumn;

class Column extends DoctrineColumn
{
    public string $publicName;
    public string $snakeName;
    public string $studName;
    public string $alias;
    public string $aliasSnakeName;
    public string $aliasStudName;

    /**
     * The DB type of this column. Possible DB types vary according to the type of DBMS.
     */
    public string $dbType;

    /**
     * phpDoc property type.
     */
    public string $phpDocType;

    /**
     * Laravel casting type of this column. Possible PHP types include:
     * integer, real, float, double, string, boolean, object, array, collection, date, datetime, and timestamp.
     */
    public string $laravelType;

    /**
     * Laravel php type returned by Eloquent.
     */
    public string $castType;

    /**
     * Whether if the column is a date or not.
     */
    public bool $isDate;

    /**
     * Whether if the column is unsigned or not.
     */
    public bool $unsigned;

    /**
     * Whether if the column is nullable or not.
     */
    public bool $nullable;
}
