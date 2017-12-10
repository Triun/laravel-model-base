<?php

namespace Triun\ModelBase\Utils;

use Exception;
use InvalidArgumentException;
use Doctrine\DBAL\Types\Type;
use Triun\ModelBase\Definitions\Table;
use Triun\ModelBase\Definitions\Column;
use Triun\ModelBase\Lib\ConnectionUtilBase;

/**
 * Class SchemaUtil
 *
 * @package Triun\ModelBase\Utils
 */
class SchemaUtil extends ConnectionUtilBase
{
    /**
     * @var \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    protected $schema;

    /**
     * @var callback[]|callable[]
     */
    protected static $table_callbacks = [];

    /**
     * @var callback[]|callable[]
     */
    protected static $column_callbacks = [];

    /**
     * @var callback[]|callable[]
     */
    protected static $cast_callbacks = [];

    /**
     * @param callback|callable $callback
     *
     * @throws Exception
     */
    public static function registerTableCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Table Callback not callable');
        }

        self::$table_callbacks[] = $callback;
    }

    /**
     * @param callback|callable $callback
     *
     * @throws Exception
     */
    public static function registerColumnCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Column Callback not callable');
        }

        self::$column_callbacks[] = $callback;
    }

    /**
     * @param callback|callable $callback
     *
     * @throws Exception
     */
    public static function registerCastCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Cast Callback not callable');
        }

        self::$cast_callbacks[] = $callback;
    }

    /**
     * Retrieve doctrine scheme manager for the given connection.
     *
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function schema()
    {
        if ($this->schema === null) {
            $this->schema = $this->conn->getDoctrineSchemaManager();
            $this->platformMapping($this->schema);
        }

        return $this->schema;
    }

    /**
     * Apply platform mapping from the config file
     *
     * @param \Doctrine\DBAL\Schema\AbstractSchemaManager $schema
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function platformMapping($schema)
    {
        $databasePlatform = $schema->getDatabasePlatform();
        $databasePlatform->registerDoctrineTypeMapping('enum', 'string');

        $platformName = $databasePlatform->getName();
        $customTypes = $this->config(
            "doctrine.dbal.driver_mapping_types.{$platformName}",
            $this->config('doctrine.dbal.mapping_types', [])
        );
        foreach ($customTypes as $yourTypeName => $doctrineTypeName) {
            $databasePlatform->registerDoctrineTypeMapping($yourTypeName, $doctrineTypeName);
        }
    }

    /**
     * @return string[]
     */
    public function getTableExceptions()
    {
//        return config('model-base.bulk.except', ['migrations']);
        return $this->config()->get('bulk.except', ['migrations']);
    }

    /**
     * Return an array of the tables names, from the connection given, excluding the exceptions set in the
     * configuration file.
     *
     * @param string[] $except Tables exceptions.
     *
     * @return \string[]
     */
    public function getTableNames($except = null)
    {
        if ($except === null) {
            $except = $this->getTableExceptions();
        }

        $tables = [];
        foreach ($this->conn->getDoctrineSchemaManager()->listTableNames() as $row) {
            $row = (array)$row;
            $table = array_shift($row);
            if (array_search($table, $except) === false) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    /**
     * @param string $tableName
     *
     * @return bool
     */
    public function hasTable($tableName)
    {
        return $this->conn->getSchemaBuilder()->hasTable($tableName);
        //return $this->_conn->affectingStatement("SHOW TABLES LIKE ?", [$tableName]);
    }

    /**
     * @param string $tableName
     *
     * @return \Triun\ModelBase\Definitions\Table
     * @throws \Exception
     */
    public function table($tableName)
    {
        return $this->listTableDetails($tableName);
    }

    /**
     * @param string $tableName
     *
     * @return \Triun\ModelBase\Definitions\Table
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function listTableDetails($tableName)
    {
        //$table = $this->schema()->listTableDetails($tableName);

        $columns = $this->listTableColumns($tableName);

        $foreignKeys = [];
        if ($this->schema()->getDatabasePlatform()->supportsForeignKeyConstraints()) {
            $foreignKeys = $this->schema()->listTableForeignKeys($tableName);
        }
        $indexes = $this->schema()->listTableIndexes($tableName);

        $table = new Table($tableName, $columns, $indexes, $foreignKeys, false, []);

        // Table callbacks
        foreach (self::$table_callbacks as $callback) {
            call_user_func($callback, $table, $this);
        }

        return $table;
    }

    /**
     * @param string $tableName
     *
     * @return \Triun\ModelBase\Definitions\Column[]
     * @throws \Exception
     */
    public function listTableColumns($tableName)
    {
        $doctrineColumns = $this->schema()->listTableColumns($tableName);

        if (count($doctrineColumns) == 0 && !$this->hasTable($tableName)) {
            throw new InvalidArgumentException("Table $tableName not found in {$this->conn->getName()} connection.");
        }

        $this->tableColumnsFixes($doctrineColumns, $tableName);

        $columns = [];

        foreach ($doctrineColumns as $key => $doctrineColumn) {
            $column = new Column($doctrineColumn->getName(), $doctrineColumn->getType(), $doctrineColumn->toArray());

            // snake_name and StudyName
            $column->snakeName      = $this->snakeCase($column->getName());
            $column->studName       = studly_case($column->getName());
            $column->publicName     = $this->config('snakeAttributes') ? $column->snakeName : $column->getName();

            // Alias
            $column->alias          = $this->aliasName($column->getName());
            if ($column->alias !== null) {
                $column->aliasSnakeName = $this->snakeCase($column->alias);
                $column->aliasStudName  = studly_case($column->alias);
                $column->publicName     = $this->config('snakeAttributes') ? $column->aliasSnakeName : $column->alias;
                $column->setComment(trim($column->getComment() . ' ' . 'Alias of ' . $column->getName()));
            }

            $column->dbType         = $column->getType()->getName();
            $column->castType       = $this->getLaravelCastType($column, $tableName);
            $column->isDate         = $this->isDate($column);
            $column->phpDocType     = $this->convertToPhpDoc($column);

            // Columns callbacks
            foreach (self::$column_callbacks as $callback) {
                call_user_func($callback, $column, $this, $tableName);
            }

            $columns[$key] = $column;
        }

        return $columns;
    }

    /**
     * This method will, depending of the configuration, fix or not, the length of the columns and the tinyint.
     *
     * Doctrine, by default, uses boolean for tinyint, no matters if the length is bigger than 1.
     * It also uses the maximum database length, instead of the used one.
     *
     * @param \Doctrine\DBAL\Schema\Column[] $columns
     * @param string                         $tableName
     *
     * @throws Exception
     */
    protected function tableColumnsFixes($columns, $tableName)
    {
        $real_length = $this->config('doctrine.dbal.real_length', true);
        $real_tinyint = $this->config('doctrine.dbal.real_tinyint', true);

        if ($real_length !== true && $real_tinyint !== true) {
            return;
        }

        $platformColumns = $this->getPlatformColumns($tableName);

        foreach ($columns as $column) {
            if (!isset($platformColumns[$column->getName()])) {
                throw new Exception("Column {$column->getName()} not found in " . implode(', ', $platformColumns));
            }

            $raw = $platformColumns[$column->getName()];

            // Set the real length
            if ($real_length === true) {
                if ($column->getLength() !== $raw['length']) {
                    // echo "{$column->getName()} length updated from " . var_export($column->getLength(), true) .
                    //     " to " . var_export($raw['length'], true) . "." . PHP_EOL;
                    $column->setLength($raw['length']);
                }
            }

            // Use Small Int if it's a Boolean with more than 1 bit.
            if ($real_tinyint === true) {
                if ($column->getType()->getName() === Type::BOOLEAN && $raw['length'] > 1) {
                    //echo "{$column->getName()} updated from boolean to smallint" . PHP_EOL;
                    $column->setType(Type::getType(Type::SMALLINT));
                }
            }
        }
    }

    /**
     * @param string $tableName
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getPlatformColumns($tableName)
    {
        $sql = $this->schema()->getDatabasePlatform()->getListTableColumnsSQL($tableName);

        $platformColumns = [];
        foreach ($this->conn->getDoctrineConnection()->fetchAll($sql) as $tableColumn) {
            $tableColumn = array_change_key_case($tableColumn, CASE_LOWER);

            $dbType = strtolower($tableColumn['type']);
            $tableColumn['db_type'] = $dbType;
            $tableColumn['type'] = strtok($dbType, '(), ');

            if (!isset($tableColumn['length'])) {
                $length = strtok('(), ');
                $tableColumn['length'] = $length === false ? null : (int)$length;
            }

            $platformColumns[$tableColumn['field']] = $tableColumn;
        }

        return $platformColumns;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function snakeCase($name)
    {
        static $rename;

        // Load rename rules
        if ($rename === null) {
            $rename = $this->config('column.camel_to_snake', []);
        }

        if (isset($rename[$name])) {
            return strtolower($rename[$name]);
        }

        return snake_case($name);
    }

    /**
     * @param string $name
     *
     * @return string|null
     */
    public function aliasName($name)
    {
        static $rules;

        // Load column aliases renaming rules
        if ($rules === null) {
            $rules = $this->config('column.aliases', []);
        }

        // Except
        // If there is a match, it will skip this alias naming.
        if (isset($rules['except']) && in_array($name, $rules['except'])) {
            return null;
        }

        // Force rename
        // If there is a match, none of the following renames rules will be processed.
        if (isset($rules['force']) && isset($rules['force'][$name]) && !empty($rules['force'][$name])) {
            return strtolower($rules['force'][$name]);
        }

        $alias = $name;

        // Pre rename
        // Rename it before the other rules are applied.
        if (isset($rules['pre']) && isset($rules['pre'][$alias]) && !empty($rules['pre'][$alias])) {
            $alias = $rules['pre'][$alias];
        }

        // Remove Prefixes
        // If the column name start with any of the words in the list, it will remove it.
        if (isset($rules['prefix'])) {
            $alias = $this->removePrefix($alias, $rules['prefix']);
        }

        // Remove Suffixes
        // If the column name ends with any of the words in the list, it will remove it.
        if (isset($rules['suffix'])) {
            $alias = $this->removeSuffix($alias, $rules['suffix']);
        }

        // Post rename
        // Rename it after the other rules are applied.
        if (isset($rules['post']) && isset($rules['post'][$alias]) && !empty($rules['post'][$alias])) {
            $alias = $rules['post'][$alias];
        }

        if ($this->snakeCase($alias) === $this->snakeCase($name)) {
            return null;
        }

        return $alias;
    }

    /**
     * Remove a given substring at the beginning of a given string.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    protected function removePrefix($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && $needle !== $haystack && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                return substr($haystack, strlen($needle));
            }
        }

        return $haystack;
    }

    /**
     * Remove a given substring at the end of a given string.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    protected function removeSuffix($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== $haystack && substr($haystack, -strlen($needle)) === (string) $needle) {
                return substr($haystack, 0, -strlen($needle));
            }
        }

        return $haystack;
    }

    /**
     * @param \Doctrine\DBAL\Schema\Column $column
     * @param string                       $tableName
     *
     * @return string
     * @throws \Exception
     */
    public function getLaravelCastType($column, $tableName)
    {
        $columnType = $column->getType()->getName();

        foreach ($this->config('cast', []) as $cast) {
            if (!isset($cast['cast_type'])) {
                throw new Exception("Cast type not defined");
            }
            if (!$this->match($cast['field'], $column->getName())) {
                continue;
            }
            if (isset($cast['db_type']) && !$this->match($cast['db_type'], $columnType)) {
                continue;
            }
            if (isset($cast['table']) && !$this->match($cast['table'], $tableName)) {
                continue;
            }
            if (isset($cast['connection']) && !$this->match($cast['connection'], $this->connection()->getName())) {
                continue;
            }

            return $cast['cast_type'];
        }

        // Cast Types:
        // integer, real, float, double, string, boolean, object, array, collection, date, datetime, and timestamp
        // TODO: Add timestamp checking.
        switch ($columnType) {
            case Type::STRING:
            case Type::TEXT:
                $castType = 'string';
                break;
            case Type::INTEGER:
            case Type::SMALLINT:
                $castType = 'integer';
                break;
            case Type::BIGINT:
                $castType = 'real';
                break;
            case Type::DECIMAL:
                $castType = 'double';
                break;
            case Type::FLOAT:
                $castType = 'float';
                break;
            case Type::BOOLEAN:
                $castType = 'boolean';
                break;
            case Type::DATE:
                $castType = 'date';
                break;
            case Type::DATETIME:
            case Type::DATETIMETZ:
            case Type::TIME:
                $castType = 'datetime';
                break;
            case Type::TARRAY:
            case Type::SIMPLE_ARRAY:
            case Type::JSON_ARRAY:
                $castType = 'array';
                break;
            case Type::OBJECT:
                $castType = 'object';
                break;
            case Type::BINARY:
            case Type::BLOB:
            case Type::GUID:
            default:
                $castType = null;
        }

        // Cast callbacks
        foreach (self::$cast_callbacks as $callback) {
            call_user_func($callback, $column, $tableName, $this->connection(), $castType);
        }

        return $castType;
    }

    /**
     * @param \Triun\ModelBase\Definitions\Column
     *
     * @return bool
     */
    public function isDate(Column $column)
    {
        if ($this->config('dates', true) !== true) {
            return false;
        }

        $columnType = $column->getType()->getName();

        // Cast Types:
        // integer, real, float, double, string, boolean, object, array, collection, date, datetime, and timestamp
        switch ($columnType) {
            case Type::DATE:
            case Type::DATETIME:
            case Type::DATETIMETZ:
            case Type::TIME:
                return true;
        }

        return false;
    }

    /**
     * @param \Triun\ModelBase\Definitions\Column $column
     *
     * @return string
     * @throws Exception
     */
    protected function convertToPhpDoc($column)
    {
        if ($column->isDate === null) {
            throw new Exception('Not defined if the column is date or not.');
        }

        if ($column->isDate) {
            return '\\'.\Carbon\Carbon::class;
        } else {
            $type = $column->getType()->getName();
            switch ($type) {
                case 'string':
                case 'text':
                case 'date':
                case 'time':
                case 'guid':
                case 'datetimetz':
                case 'datetime':
                    return 'string';
                    break;
                case 'integer':
                case 'bigint':
                case 'smallint':
                    return 'integer';
                    break;
                case 'decimal':
                case 'float':
                    return 'float';
                    break;
                case 'boolean':
                    return 'boolean';
                    break;
                default:
                    return 'mixed';
                    break;
            }
        }
    }
}
