<?php

namespace Triun\ModelBase\Utils;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column as DBALColumn;
use Exception;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Triun\ModelBase\Definitions\Column;
use Triun\ModelBase\Definitions\Table;
use Triun\ModelBase\Helpers\DBALHelper;
use Triun\ModelBase\Helpers\TypeHelper;
use Triun\ModelBase\Lib\ConnectionUtilBase;

/**
 * Class SchemaUtil
 *
 * @package Triun\ModelBase\Utils
 */
class SchemaUtil extends ConnectionUtilBase
{
    /**
     * @var callback[]|callable[]
     */
    private static $table_callbacks = [];

    /**
     * @var callback[]|callable[]
     */
    private static $column_callbacks = [];

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
        TypeHelper::registerCastCallback($callback);
    }

    /**
     * Initialize Util
     */
    protected function init()
    {
        parent::init();

        TypeHelper::$shortTypes = $this->config('short_types', true);
    }

    /**
     * Retrieve doctrine scheme manager for the given connection.
     *
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     * @throws \Doctrine\DBAL\DBALException
     */
    private function schema(): AbstractSchemaManager
    {
        //$this->conn->getDoctrineSchemaManager();
        return DBALHelper::platformMapping(
            DBALHelper::getSchema($this->conn->getDoctrineConnection()),
            $this->config('doctrine.dbal.mapping_types'),
            $this->config('doctrine.dbal.driver_mapping_types')
        );
    }

    /**
     * @param \Doctrine\DBAL\Schema\AbstractSchemaManager $schema
     * @param string                                      $tableName
     *
     * @return DBALColumn[]
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getDoctrineColumns(AbstractSchemaManager $schema, string $tableName)
    {
        $doctrineColumns = $schema->listTableColumns($tableName);

        if (count($doctrineColumns) == 0 && !$this->hasTable($tableName)) {
            throw new InvalidArgumentException("Table $tableName not found in {$this->conn->getName()} connection.");
        }

        DBALHelper::tableColumnsFixes(
            $this->connection()->getDoctrineConnection(),
            $doctrineColumns,
            $tableName,
            $this->config('doctrine.dbal.real_length', true),
            $this->config('doctrine.dbal.real_tinyint', true)
        );

        return $doctrineColumns;
    }

    /**
     * @return string[]
     */
    public function getTableExceptions()
    {
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
            $row   = (array)$row;
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

        $schema  = $this->schema();
        $columns = $this->listTableColumns($schema, $tableName);

        $foreignKeys = $schema->getDatabasePlatform()->supportsForeignKeyConstraints() ?
            $schema->listTableForeignKeys($tableName) : [];
        $indexes     = $schema->listTableIndexes($tableName);

        $table = new Table($tableName, $columns, $indexes, $foreignKeys, false, []);

        // Table callbacks
        foreach (self::$table_callbacks as $callback) {
            call_user_func($callback, $table, $this);
        }

        return $table;
    }

    /**
     * @param \Doctrine\DBAL\Schema\AbstractSchemaManager $schema
     * @param string                                      $tableName
     *
     * @return \Triun\ModelBase\Definitions\Column[]
     * @throws \Exception
     */
    private function listTableColumns(AbstractSchemaManager $schema, $tableName)
    {
        $columns = [];
        foreach ($this->getDoctrineColumns($schema, $tableName) as $key => $doctrineColumn) {
            $column = new Column($doctrineColumn->getName(), $doctrineColumn->getType(), $doctrineColumn->toArray());

            // snake_name and StudyName
            $column->snakeName  = $this->snakeCase($column->getName());
            $column->studName   = Str::studly($column->getName());
            $column->publicName = $this->config('snakeAttributes') ? $column->snakeName : $column->getName();

            // Alias
            $column->alias = $this->aliasName($column->getName());
            if ($column->alias !== null) {
                $column->aliasSnakeName = $this->snakeCase($column->alias);
                $column->aliasStudName  = Str::studly($column->alias);
                $column->publicName     = $this->config('snakeAttributes') ? $column->aliasSnakeName : $column->alias;
                $column->setComment(trim($column->getComment() . ' ' . 'Alias of ' . $column->getName()));
            }

            $column->dbType      = $column->getType()->getName();
            $column->laravelType = TypeHelper::getLaravelType($column->getType());
            $column->castType    =
                TypeHelper::getLaravelCastType($column, $tableName, $this->config(), $this->connection());
            $column->isDate      = $this->isDate($column);
            $column->phpDocType  = $this->convertToPhpDoc($column);

            // Columns callbacks
            foreach (self::$column_callbacks as $callback) {
                call_user_func($callback, $column, $this, $tableName);
            }

            $columns[$key] = $column;
        }

        return $columns;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function snakeCase($name)
    {
        static $rename;

        // Load rename rules
        if ($rename === null) {
            $rename = $this->config('column.camel_to_snake', []);
        }

        if (isset($rename[$name])) {
            return strtolower($rename[$name]);
        }

        return Str::snake($name);
    }

    /**
     * @param string $name
     *
     * @return string|null
     */
    private function aliasName(string $name): ?string
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
     * @param string       $haystack
     * @param string|array $needles
     *
     * @return string
     */
    private function removePrefix(string $haystack, $needles): string
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && $needle !== $haystack && substr($haystack, 0, strlen($needle)) === (string)$needle) {
                return substr($haystack, strlen($needle));
            }
        }

        return $haystack;
    }

    /**
     * Remove a given substring at the end of a given string.
     *
     * @param string       $haystack
     * @param string|array $needles
     *
     * @return string
     */
    private function removeSuffix(string $haystack, $needles): string
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== $haystack && substr($haystack, -strlen($needle)) === (string)$needle) {
                return substr($haystack, 0, -strlen($needle));
            }
        }

        return $haystack;
    }

    /**
     * @param \Triun\ModelBase\Definitions\Column
     *
     * @return bool
     */
    public function isDate(Column $column): bool
    {
        if (true !== $this->config('dates', true)) {
            return false;
        }

        return TypeHelper::isDateTime($column->getType());
    }

    /**
     * @param \Triun\ModelBase\Definitions\Column $column
     *
     * @return string
     * @throws Exception
     */
    private function convertToPhpDoc(Column $column): string
    {
        if ($column->isDate === null) {
            throw new Exception('Not defined if the column is date or not.');
        }

        if ($column->isDate) {
            return '\\' . \Carbon\Carbon::class;
        }

        return TypeHelper::getPhpDocType($column->getType());
    }
}
