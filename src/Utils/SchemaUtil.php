<?php

namespace Triun\ModelBase\Utils;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column as DBALColumn;
use Doctrine\DBAL\Types\Types;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Triun\ModelBase\Definitions\Column;
use Triun\ModelBase\Definitions\Table;
use Triun\ModelBase\Helpers\DBALHelper;
use Triun\ModelBase\Helpers\TypeHelper;
use Triun\ModelBase\Lib\ConnectionUtilBase;

class SchemaUtil extends ConnectionUtilBase
{
    /**
     * @see \Doctrine\DBAL\Schema\MySQLSchemaManager::_getPortableTableColumnDefinition()
     * @see \Doctrine\DBAL\Schema\PostgreSQLSchemaManager::_getPortableTableColumnDefinition()
     * @see \Doctrine\DBAL\Schema\SQLServerSchemaManager::_getPortableTableColumnDefinition()
     * @see \Doctrine\DBAL\Schema\SqliteSchemaManager::_getPortableTableColumnDefinition()
     * @see \Doctrine\DBAL\Schema\OracleSchemaManager::_getPortableTableColumnDefinition()
     * @see \Doctrine\DBAL\Schema\DB2SchemaManager::_getPortableTableColumnDefinition()
     * @see \Doctrine\DBAL\Schema\AbstractSchemaManager::_getPortableTableColumnDefinition()
     */
    private const SCHEMA_COMMON = [
        'length',
        'unsigned',
        'fixed',
        'default',
        'notnull',
        'scale',
        'precision',
        'autoincrement',
        'comment',
        //'platformOptions', only DB2SchemaManager
    ];

    /**
     * @var callable[]
     */
    private static array $table_callbacks = [];

    /**
     * @var callable[]
     */
    private static array $column_callbacks = [];

    /**
     * @throws Exception
     */
    public static function registerTableCallback(callable $callback): void
    {
        if (!is_callable($callback)) {
            throw new Exception('Table Callback not callable');
        }

        self::$table_callbacks[] = $callback;
    }

    /**
     * @throws Exception
     */
    public static function registerColumnCallback(callable $callback): void
    {
        if (!is_callable($callback)) {
            throw new Exception('Column Callback not callable');
        }

        self::$column_callbacks[] = $callback;
    }

    /**
     * @throws Exception
     */
    public static function registerCastCallback(callable $callback): void
    {
        TypeHelper::registerCastCallback($callback);
    }

    protected function init(): void
    {
        parent::init();

        TypeHelper::$shortTypes = $this->config('short_types', true);
    }

    /**
     * Retrieve doctrine scheme manager for the given connection.
     *
     * @throws \Doctrine\DBAL\Exception
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
     * @return DBALColumn[]
     */
    private function getDoctrineColumns(AbstractSchemaManager $schema, string $tableName): array
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
    public function getTableExceptions(): array
    {
        return $this->config()->get('bulk.except', ['migrations']);
    }

    /**
     * Return an array of the tables names, from the connection given, excluding the exceptions set in the
     * configuration file.
     *
     * @param string[]|null $except Tables exceptions.
     *
     * @return string[]
     */
    public function getTableNames(?array $except = null): array
    {
        if ($except === null) {
            $except = $this->getTableExceptions();
        }

        $tables = [];
        foreach ($this->conn->getDoctrineSchemaManager()->listTableNames() as $row) {
            $row   = (array)$row;
            $table = array_shift($row);
            if (!in_array($table, $except)) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    public function hasTable(string $tableName): bool
    {
        return $this->conn->getSchemaBuilder()->hasTable($tableName);
        //return $this->_conn->affectingStatement("SHOW TABLES LIKE ?", [$tableName]);
    }

    /**
     * @throws Exception
     */
    public function table(string $tableName): Table
    {
        return $this->listTableDetails($tableName);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function listTableDetails(string $tableName): Table
    {
        //$table = $this->schema()->listTableDetails($tableName);

        $schema  = $this->schema();
        $columns = $this->listTableColumns($schema, $tableName);

        $uniqueConstraints = []; // TODO: New to implement
        $foreignKeys       = $schema->getDatabasePlatform()->supportsForeignKeyConstraints() ?
            $schema->listTableForeignKeys($tableName) : [];
        $indexes           = $schema->listTableIndexes($tableName);

        $table = new Table($tableName, $columns, $indexes, $uniqueConstraints, $foreignKeys, []);

        // Table callbacks
        foreach (self::$table_callbacks as $callback) {
            call_user_func($callback, $table, $this);
        }

        return $table;
    }

    /**
     * @return Column[]
     * @throws Exception
     */
    private function listTableColumns(AbstractSchemaManager $schema, string $tableName): array
    {
        $columns = [];
        foreach ($this->getDoctrineColumns($schema, $tableName) as $key => $doctrineColumn) {
            $column = new Column(
                $doctrineColumn->getName(),
                $doctrineColumn->getType(),
                Arr::only($doctrineColumn->toArray(), self::SCHEMA_COMMON),
            );

            // We are not including platform options such as `charset`, `collation` or `jsonb`,
            // because we don't need them

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

            $column->nullable = !$doctrineColumn->getNotnull();
            $column->unsigned = $doctrineColumn->getUnsigned();

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

    private function snakeCase(string $name): string
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
     */
    private function removePrefix(string $haystack, array|string $needles): string
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
     */
    private function removeSuffix(string $haystack, array|string $needles): string
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== $haystack && substr($haystack, -strlen($needle)) === (string)$needle) {
                return substr($haystack, 0, -strlen($needle));
            }
        }

        return $haystack;
    }

    public function isDate(Column $column): bool
    {
        if (true !== $this->config('dates', true)) {
            return false;
        }

        return TypeHelper::isDateTime($column->getType());
    }

    /**
     * @throws Exception
     */
    private function convertToPhpDoc(Column $column): string
    {
        $type = $this->getToPhpDocBaseType($column);

        if ($column->nullable) {
            $type .= '|null';
        }

        return $type;
    }

    /**
     * @throws Exception
     */
    private function getToPhpDocBaseType(Column $column): string
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
