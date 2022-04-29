<?php

declare(strict_types=1);

namespace Triun\ModelBase\Helpers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use InvalidArgumentException;

abstract class DBALHelper
{
    /**
     * Recommended configuration for the driver mappings
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
     * @throws \Doctrine\DBAL\Exception
     */
    public static function getSchema(Connection $conn): AbstractSchemaManager
    {
        return $conn->getDriver()->getSchemaManager($conn, $conn->getDatabasePlatform());
    }

    /**
     * This method will, depending on the configuration, fix or not, the length of the columns and the tinyint.
     *
     * Doctrine, by default, uses boolean for tinyint, no matters if the length is bigger than 1.
     * It also uses the maximum database length, instead of the used one.
     *
     * @param Connection $conn
     * @param Column[]   $columns
     * @param string     $tableName
     * @param bool       $realLength  (default true)
     * @param bool       $realTinyInt (default true)
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public static function tableColumnsFixes(
        Connection $conn,
        array $columns,
        string $tableName,
        bool $realLength = true,
        bool $realTinyInt = true
    ): void {
        if (true !== $realLength && true !== $realTinyInt) {
            return;
        }

        $platformColumns = self::getPlatformColumns($conn, $tableName);

        foreach ($columns as $column) {
            if (!isset($platformColumns[$column->getName()])) {
                throw new InvalidArgumentException("Column {$column->getName()} not found in " .
                                                   implode(', ', $platformColumns));
            }

            $raw = $platformColumns[$column->getName()];

            self::fixRealLength($column, $raw['length']);
            self::fixSmallInt($column, $raw['length']);
        }
    }

    /**
     * Set the real length
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
     * @throws \Doctrine\DBAL\Exception
     * @see \Doctrine\DBAL\Exception::unknownColumnType()
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
     * @throws \Doctrine\DBAL\Exception
     */
    private static function getPlatformColumns(Connection $conn, string $tableName): array
    {
        $sql = self::getSchema($conn)->getDatabasePlatform()->getListTableColumnsSQL($tableName);

        $platformColumns = [];
        foreach ($conn->fetchAllAssociative($sql) as $tableColumn) {
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
     * @param AbstractSchemaManager $schema
     * @param array                 $defaultMappings (default [])
     * @param array                 $driversMappings (default {@see DBALHelper::DEFAULT_DRIVER_MAPPINGS})
     *
     * @return AbstractSchemaManager
     * @throws \Doctrine\DBAL\Exception
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
