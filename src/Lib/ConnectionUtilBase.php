<?php

namespace Triun\ModelBase\Lib;

use Illuminate\Database\Connection;
use Triun\ModelBase\ModelBaseConfig;

abstract class ConnectionUtilBase extends UtilBase
{
    protected Connection $conn;

    public function __construct(Connection $connection, ModelBaseConfig $config)
    {
        $this->conn = $connection;

        parent::__construct($config);
    }

    public function connection(): Connection
    {
        return $this->conn;
    }
}
