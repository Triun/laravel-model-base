<?php

namespace Triun\ModelBase\Lib;

use Illuminate\Database\Connection;
use Triun\ModelBase\ModelBaseConfig;

/**
 * Class ConnectionUtilBase
 *
 * @package Triun\ModelBase\Lib
 */
abstract class ConnectionUtilBase extends UtilBase
{
    /**
     * Illuminate connection
     *
     * @var \Illuminate\Database\Connection
     */
    protected $conn;

    /**
     * ModelBaseUtil constructor.
     *
     * @param \Illuminate\Database\Connection  $connection
     * @param \Triun\ModelBase\ModelBaseConfig $config
     */
    public function __construct(Connection $connection, ModelBaseConfig $config)
    {
        $this->conn = $connection;

        parent::__construct($config);
    }

    /**
     * Get the database connection instance used by this util.
     *
     * @return \Illuminate\Database\Connection
     */
    public function connection()
    {
        return $this->conn;
    }
}
