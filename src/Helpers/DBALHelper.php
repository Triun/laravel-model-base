<?php

declare(strict_types=1);

namespace Triun\ModelBase\Helpers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use InvalidArgumentException;

/**
 * Class DBALHelper
 *
 * @package Triun\ModelBase\Helpers
 */
abstract class DBALHelper
{
    /**
     * Recommended configuration for the driver mappings
     *
     * @var array
     */
    const DEFAULT_DRIVER_MAPPINGS = [
        'mysql' => [
            'enum' => 'string',
            //'tinyint' => 'smallint',
        ],
        'mssql' => [
            'xml' => 'string',
        ],
    ];

    /**
     * @param \Doctrine\DBAL\Connection $conn
     *
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    public static function getSchema(Connection $conn): AbstractSchemaManager
    {
        return $conn->getDriver()->getSchemaManager($conn);
    }

    /**
     * This method will, depending of the configuration, fix or not, the length of the columns and the tinyint.
     *
     * Doctrine, by default, uses boolean for tinyint, no matters if the length is bigger than 1.
     * It also uses the maximum database length, instead of the used one.
     *
     * @param \Doctrine\DBAL\Connection      $conn
     * @param \Doctrine\DBAL\Schema\Column[] $columns
     * @param string                         $tableName
     * @param bool                           $realLength  (default true)
     * @param bool                           $realTinyInt (default true)
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function tableColumnsFixes(
        Connection $conn,
        array $columns,
        string $tableName,
        bool $realLength = true,
        bool $realTinyInt = true
    ) {
        if (true !== $realLength && true !== $realTinyInt) {
            return;
        }

        $platformColumns = static::getPlatformColumns($conn, $tableName);

        foreach ($columns as $column) {
            if (!isset($platformColumns[$column->getName()])) {
                throw new InvalidArgumentException("Column {$column->getName()} not found in " .
                                                   implode(', ', $platformColumns));
            }

            $raw = $platformColumns[$column->getName()];

            static::fixRealLength($column, $raw['length']);
            static::fixSmallInt($column, $raw['length']);
        }
    }

    /**
     * Set the real length
     *
     * @param \Doctrine\DBAL\Schema\Column $column
     * @param int|null                     $length
     *
     * @return void
     */
    private static function fixRealLength(Column $column, ?int $length): void
    {
        if ($column->getLength() === $length) {
            return;
        }

        // echo "{$column->getName()} length updated from " . var_export($column->getLength(), true) .
        //     " to " . var_export($length, true) . "." . PHP_EOL;

        $column->setLength($length);
    }

    /**
     * Use Small Int if it's a Boolean with more than 1 bit.
     *
     * @param \Doctrine\DBAL\Schema\Column $column
     * @param int|null                     $length
     *
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     * @see \Doctrine\DBAL\DBALException::unknownColumnType()
     */
    private static function fixSmallInt(Column $column, ?int $length): void
    {
        if ($column->getType()->getName() !== Types::BOOLEAN) {
            return;
        }

        if (null === $length || $length <= 1) {
            return;
        }

        //echo "{$column->getName()} updated from boolean to smallint" . PHP_EOL;

        $column->setType(Type::getType(Types::SMALLINT));
    }

    /**
     * @param \Doctrine\DBAL\Connection $conn
     * @param string                    $tableName
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private static function getPlatformColumns(Connection $conn, string $tableName)
    {
        $sql = static::getSchema($conn)->getDatabasePlatform()->getListTableColumnsSQL($tableName);

        $platformColumns = [];
        foreach ($conn->fetchAll($sql) as $tableColumn) {
            $tableColumn = array_change_key_case($tableColumn, CASE_LOWER);

            $dbType                 = strtolower($tableColumn['type']);
            $tableColumn['db_type'] = $dbType;
            $tableColumn['type']    = strtok($dbType, '(), ');

            if (!isset($tableColumn['length'])) {
                $length                = strtok('(), ');
                $tableColumn['length'] = $length === false ? null : (int)$length;
            }

            $platformColumns[$tableColumn['field']] = $tableColumn;
        }

        return $platformColumns;
    }

    /**
     * Apply platform mapping types from the config file
     *
     * @param \Doctrine\DBAL\Schema\AbstractSchemaManager $schema
     * @param array|null                                  $defaultMappings (default [])
     * @param array|null                                  $driversMappings (default
     *                                                                     {@see DBALHelper::DEFAULT_DRIVER_MAPPINGS})
     *
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function platformMapping(
        AbstractSchemaManager $schema,
        array $defaultMappings = [],
        array $driversMappings = DBALHelper::DEFAULT_DRIVER_MAPPINGS
    ): AbstractSchemaManager {
        $databasePlatform = $schema->getDatabasePlatform();
        //$databasePlatform->registerDoctrineTypeMapping('enum', 'string');

        $platformName = $databasePlatform->getName();
        $customTypes  = array_key_exists($platformName, $driversMappings) ?
            $driversMappings[$platformName] :
            $defaultMappings;

        foreach ($customTypes as $yourTypeName => $doctrineTypeName) {
            $databasePlatform->registerDoctrineTypeMapping($yourTypeName, $doctrineTypeName);
        }

        return $schema;
    }
}
