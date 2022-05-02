<?php

namespace Triun\ModelBase;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Throwable;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Definitions\Table;
use Triun\ModelBase\Modifiers\PhpDocModifier;
use Triun\ModelBase\Utils\BuilderUtil;
use Triun\ModelBase\Utils\SchemaUtil;
use Triun\ModelBase\Utils\SkeletonUtil;

class Util
{
    public const AUTO = 'auto';
    public const CONFIRM = 'confirm';

    protected Connection $conn;
    protected ModelBaseConfig $config;
    protected ?Command $command;

    /**
     * @throws Exception
     */
    public function __construct(
        string|Connection|null $connection = null,
        ?Command $command = null
    ) {
        $this->conn = $this->normalizeConnection($connection);

        $this->verifyRequirements();

        $this->config = new ModelBaseConfig($this->conn);

        $this->command = $command;
    }

    /**
     * @throws Exception
     */
    protected function verifyRequirements(): void
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
     * @throws Exception
     */
    protected function normalizeConnection(string|Connection|null $connection): Connection
    {
        if ($connection instanceof Connection) {
            return $connection;
        }

        // \DB::connection($connection);
        // \App::make('db')->connection($connection);
        // \Illuminate\Container\Container::getInstance()->make($make);
        //$conn = \Illuminate\Support\Facades\DB::connection($connection);
        $conn = \Illuminate\Container\Container::getInstance()->make('db')->connection($connection);

        if (!$conn instanceof Connection) {
            throw new \RuntimeException('Expected \Illuminate\Database\Connection');
        }

        return $conn;
    }

    public function connection(): Connection
    {
        return $this->conn;
    }

    public function config(): ModelBaseConfig
    {
        return $this->config;
    }

    public function schemaUtil(): SchemaUtil
    {
        return new SchemaUtil($this->conn, $this->config);
    }

    public function skeletonUtil(): SkeletonUtil
    {
        return new SkeletonUtil($this->conn, $this->config);
    }

    public function builderUtil(): BuilderUtil
    {
        return new BuilderUtil($this->config, $this->command);
    }

    public function hasCommand(): bool
    {
        return $this->command instanceof \Illuminate\Console\Command;
    }

    public function command(): ?Command
    {
        return $this->command;
    }

    /**
     * @see \Triun\ModelBase\Lib\ModifierBase::boot()
     */
    protected function loadModifiers(): void
    {
        foreach ($this->config()->modifiers() as $modClass) {
            call_user_func([$modClass, 'boot']);
        }
    }

    /**
     * Get Doctrine table schema for the given table name.
     *
     * @throws Exception
     */
    protected function table(string $tableName): Table
    {
        return $this->schemaUtil()->table($tableName);
    }

    /**
     * @throws Exception
     */
    protected function skeleton(Table $table): Skeleton
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
     * @throws Exception
     */
    protected function modelSkeleton(Table $table, Skeleton $skeleton): Skeleton
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
     * @return int The method returns the number of bytes that were written to the file, or false on failure.
     * @throws Throwable
     */
    protected function build(Skeleton $skeleton, ?string &$path): int
    {
        return $this->builderUtil()->build($skeleton, $this->config->get('override', 'confirm'), $path);
    }

    /**
     * Build the php class object for the model and save it.
     *
     * @return int The method returns the number of bytes that were written to the file, or false on failure.
     * @throws Throwable
     */
    protected function buildModel(Skeleton $skeleton, ?string &$path): int
    {
        return $this->builderUtil()->build($skeleton, $this->config->get('model.override', 'confirm'), $path);
    }

    /**
     * @throws Exception
     */
    public function make(string $tableName, ?string &$modelBasePath = null, ?string &$modelPath = null): int
    {
        $this->loadModifiers();

        $table = $this->table($tableName);

        $skeleton = $this->skeleton($table);

        $size = $this->build($skeleton, $modelBasePath);

        if ($size >= 0 && $this->config->get('model.save', true)) {
            $modelSkeleton = $this->modelSkeleton($table, $skeleton);
            $modelSize     = $this->buildModel($modelSkeleton, $modelPath);
        }

        return $size;
    }

    public function getModelBasePath(): string
    {
        return '';
    }

    public function getModelPath(): string
    {
        return '';
    }
}
