<?php

namespace Triun\ModelBase;

use DB;
use Exception;
use Illuminate\Console\Command;
use Triun\ModelBase\Definitions\Table;
use Triun\ModelBase\Modifiers\PhpDocModifier;
use Triun\ModelBase\Utils\SchemaUtil;
use Triun\ModelBase\Utils\SkeletonUtil;
use Triun\ModelBase\Utils\BuilderUtil;
use Triun\ModelBase\Definitions\Skeleton;

/**
 * Class Util
 *
 * @package Triun\ModelBase
 */
class Util
{
    /**
     * Auto mode.
     */
    const AUTO = 'auto';

    /**
     * Confirm mode.
     */
    const CONFIRM = 'confirm';

    /**
     * Illuminate connection
     *
     * @var \Illuminate\Database\Connection
     */
    protected $conn;

    /**
     * Configuration Settings.
     *
     * @return \Triun\ModelBase\ModelBaseConfig
     */
    protected $config;

    /**
     * The output interface implementation.
     *
     * @var \Illuminate\Console\Command
     */
    protected $command;

    /**
     * ModelBaseUtil constructor.
     *
     * @param \Illuminate\Database\Connection|string|null $connection
     * @param \Illuminate\Console\Command|null            $command
     *
     * @throws \Exception
     */
    public function __construct($connection = null, Command $command = null)
    {
        $this->conn = $this->normalizeConnection($connection);

        $this->verifyRequirements();

        $this->config = new ModelBaseConfig($this->conn);

        $this->command = $command;
    }

    /**
     * Verify Util requirements.
     *
     * @throws Exception
     */
    protected function verifyRequirements()
    {
        // interface_exists('Doctrine\DBAL\Driver')
        // class_exists('Doctrine\DBAL\Connection')
        if (!interface_exists('Doctrine\DBAL\Driver')) {
            throw new Exception(__CLASS__ . ' requires Doctrine DBAL; install "doctrine/dbal".');
        } elseif (!$this->conn->isDoctrineAvailable()) {
            throw new Exception('Laravel connection is unable to access Doctrine.');
        }
    }

    /**
     * @param \Illuminate\Database\Connection|string|null $connection
     *
     * @return \Illuminate\Database\Connection
     * @throws Exception
     */
    protected function normalizeConnection($connection)
    {
        if ($connection instanceof \Illuminate\Database\Connection) {
            return $connection;
        }

        if ($connection !== null && !is_string($connection)) {
            throw new Exception('Invalid connection format');
        }

        // \DB::connection($connection);
        // \App::make('db')->connection($connection);
        // \Illuminate\Container\Container::getInstance()->make($make);
        return DB::connection($connection);
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

    /**
     * Get Configuration Settings.
     *
     * @return \Triun\ModelBase\ModelBaseConfig
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * @return \Triun\ModelBase\Utils\SchemaUtil
     */
    public function schemaUtil()
    {
        return new SchemaUtil($this->conn, $this->config);
    }

    /**
     * @return \Triun\ModelBase\Utils\SkeletonUtil
     */
    public function skeletonUtil()
    {
        return new SkeletonUtil($this->conn, $this->config);
    }

    /**
     * @return \Triun\ModelBase\Utils\BuilderUtil
     */
    public function builderUtil()
    {
        return new BuilderUtil($this->config, $this->command);
    }

    /**
     * Either if there is a command declared or not.
     *
     * @return bool
     */
    public function hasCommand()
    {
        return $this->command instanceof \Illuminate\Console\Command;
    }

    /**
     * Get The calling command.
     *
     * @return \Illuminate\Console\Command
     */
    public function command()
    {
        return $this->command;
    }

    /**
     * @see \Triun\ModelBase\Lib\ModifierBase::boot()
     */
    protected function loadModifiers()
    {
        foreach ($this->config()->modifiers() as $modClass) {
            call_user_func([$modClass, 'boot']);
        }
    }

    /**
     * Get Doctrine table schema for the given table name.
     *
     * @param  string $tableName
     *
     * @return \Triun\ModelBase\Definitions\Table
     * @throws \Exception
     */
    protected function table($tableName)
    {
        return $this->schemaUtil()->table($tableName);
    }

    /**
     * Generate Model Base Skeleton
     *
     * @param \Triun\ModelBase\Definitions\Table $table
     *
     * @return Skeleton
     * @throws \Exception
     */
    protected function skeleton($table)
    {
        return $this->skeletonUtil()->make(
            $table,
            $this->config()->getBaseClassName($table->getName()),
            $this->config()->get('extends'),
            $this->config()->modifiers(),
            true
        );
    }

    /**
     * Generate Model Skeleton
     *
     * @param \Triun\ModelBase\Definitions\Table    $table
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     *
     * @return Skeleton
     * @throws \Exception
     */
    protected function modelSkeleton(Table $table, Skeleton $skeleton)
    {
        return $this->skeletonUtil()->make(
            $table,
            $this->config()->getModelClassName($table->getName()),
            $skeleton,
            [
                PhpDocModifier::class,

                // Custom
                Modifiers\CustomModelOptionsModifier::class,
            ],
            false
        );
    }

    /**
     * Build the php class object and save it.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     * @param string                                $path
     *
     * @return int The method returns the number of bytes that were written to the file, or false on failure.
     * @throws \Exception
     */
    protected function build(Skeleton $skeleton, &$path)
    {
        return $this->builderUtil()->build($skeleton, $this->config->get('override', 'confirm'), $path);
    }

    /**
     * Build the php class object for the model and save it.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     * @param string                                $path
     *
     * @return int The method returns the number of bytes that were written to the file, or false on failure.
     * @throws \Exception
     */
    protected function buildModel(Skeleton $skeleton, &$path)
    {
        return $this->builderUtil()->build($skeleton, $this->config->get('model.override', 'confirm'), $path);
    }

    /**
     * Make model base for a table name given
     *
     * @param string $tableName
     * @param string $modelBasePath
     * @param string $modelPath
     *
     * @return int
     * @throws \Exception
     */
    public function make($tableName, &$modelBasePath = null, &$modelPath = null)
    {
        $this->loadModifiers();

        $table = $this->table($tableName);

        $skeleton = $this->skeleton($table);

        $size = $this->build($skeleton, $modelBasePath);

        if ($size >= 0 && $this->config->get('model.save', true)) {
            $modelSkeleton = $this->modelSkeleton($table, $skeleton);
            $modelSize = $this->buildModel($modelSkeleton, $modelPath);
        }

        return $size;
    }

    /**
     * @return string
     */
    public function getModelBasePath()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getModelPath()
    {
        return '';
    }
}
