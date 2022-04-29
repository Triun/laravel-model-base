<?php

namespace Triun\ModelBase;

use Illuminate\Database\Connection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Triun\ModelBase\Modifiers;

class ModelBaseConfig
{
    private const CONFIG_FILE = 'model-base';
    private const WILDCARD_CONNECTION_STUD = '{{Connection}}';
    private const WILDCARD_DRIVER_STUD = '{{Driver}}';

    /**
     * @var string[]
     */
    protected array $modifiers = [
        // Connection
        Modifiers\ConnectionModifier::class,
        // Table
        Modifiers\TableModifier::class,
        // Timestamps
        Modifiers\TimestampsModifier::class,
        // Dates
        Modifiers\DatesModifier::class,
        // Soft Deletes
        Modifiers\SoftDeletesModifier::class,
        // Attributes
        Modifiers\AttributesModifier::class,
        // CamelToSnake Attributes
        Modifiers\CamelToSnakeModifier::class,
        // For custom aliases
        Modifiers\ColumnAliasModifier::class,
        // PhpDoc tags
        Modifiers\PhpDocModifier::class,
        // PhpDoc tags
        Modifiers\AuthModifier::class,
        // Relations
        // Input transformations
        // ValueObjects
    ];

    /**
     * Util configuration.
     * This config will have the following priority:
     * - config parameters in constructor.
     * - laravel config (/config/model-base.php).
     * - default config (../config/model-base.php).
     */
    protected array $items = [];
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->items = array_merge(
            $this->loadConfig(self::CONFIG_FILE),
            $this->loadConfig(self::CONFIG_FILE . '.drivers.' . $connection->getDriverName()),
            $this->loadConfig(self::CONFIG_FILE . '.connections.' . $connection->getName())
            // $this->loadConfig(self::CONFIG_FILE.'.tables.'.$tableName),
            // $this->loadConfig(self::CONFIG_FILE.'.connections.'.$connection->getName().'.tables.'.$tableName),
        );

        $this->connection = $connection;
    }

    protected function loadConfig($name): mixed
    {
        return Config::has($name) ? Config::get($name) : [];
    }

    /**
     * Determine if the given configuration value exists.
     */
    public function has(string $key): bool
    {
        return Arr::has($this->items, $key);
    }

    /**
     * Get the specified configuration value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * fnmatch separated by |
     * http://php.net/fnmatch
     * '*gr[ae]y' is gray and grey
     * 'gray|grey' is also gray and grey
     * '*At|*_at finish in 'At' or '_at'
     *
     * @param string|string[] $rules
     * @param string          $value
     * @param bool            $case_sensitive
     */
    public function match(array|string $rules, string $value, bool $case_sensitive = false): bool
    {
        //return fnmatch($rules, $value);

        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        foreach ($rules as $rule) {
            $rule = str_replace(' ', '', $rule);

            if (!$case_sensitive) {
                $rule  = strtolower($rule);
                $value = strtolower($value);
            }

            if (fnmatch($rule, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the combination of mandatory plus custom modifiers.
     *
     * The custom modifiers are defined in the config file.
     *
     * @return string[]
     */
    public function modifiers(): array
    {
        return array_merge($this->modifiers, $this->get('modifiers'));
    }

    /**
     * Generate the class name from the table name and the config data given.
     */
    public function getBaseClassName(string $tableName): string
    {
        return $this->getClassName(
            $tableName,
            $this->get('namespace'),
            $this->get('prefix'),
            $this->get('suffix'),
            array_merge($this->get('table.renames', []), $this->get('renames', [])),
            $this->get('table.prefixes')
        );
    }

    public function getModelClassName(string $tableName): string
    {
        return $this->getClassName(
            $tableName,
            $this->get('model.namespace'),
            $this->get('model.prefix'),
            $this->get('model.suffix'),
            array_merge($this->get('table.renames', []), $this->get('renames', [])),
            $this->get('table.prefixes')
        );
    }

    public function getAddOnClassName(string $className): string
    {
        return $this->getClassName(
            class_basename($className),
            $this->get('addons.namespace'),
            $this->get('addons.prefix'),
            $this->get('addons.suffix'),
            array_merge($this->get('addons.table.renames', []), $this->get('addons.renames', [])),
            $this->get('addons.table.prefixes', [])
        );
    }

    /**
     * @param string   $tableName
     * @param string   $namespace
     * @param string   $prefix
     * @param string   $suffix
     * @param string[] $tableRenames
     * @param string[] $tablePrefixes
     *
     * @return string
     */
    protected function getClassName(
        string $tableName,
        string $namespace,
        string $prefix,
        string $suffix,
        array $tableRenames,
        array $tablePrefixes
    ): string {
        $name = Str::studly($this->renameTableName($tableName, $tableRenames, $tablePrefixes));

        return str_replace([
            self::WILDCARD_CONNECTION_STUD,
            self::WILDCARD_DRIVER_STUD,
        ], [
            Str::studly($this->connection->getName()),
            Str::studly($this->connection->getDriverName()),
        ], $namespace . '\\' . $prefix . $name . $suffix);
    }

    protected function renameTableName(string $tableName, array $tableRenames, array $tablePrefixes): string
    {
        if (isset($tableRenames[$tableName])) {
            return trim($tableRenames[$tableName]);
        }

        foreach ($tablePrefixes as $prefix) {
            if (Str::startsWith($tableName, $prefix)) {
                return Str::singular(Str::replaceFirst($prefix, '', $tableName));
            }
        }

        return Str::singular($tableName);
    }
}
