<?php

namespace Triun\ModelBase\Console;

use DB;
use Triun\ModelBase\Util;
use Illuminate\Database\Connection;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MakeBulkCommand
 *
 * @package Triun\ModelBase\Console
 */
class MakeBulkCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:model-base-bulk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Eloquent Base model class, for each table, from a connection given.';

    /**
     * @var \Triun\ModelBase\Util
     */
    protected $util;

    /**
     * @var string[]
     */
    protected $tables;

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this
            ->addOption(
                'connection',
                'c',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'The connection we want to run.',
                config('model-base.bulk.connections', null)
            );
    }

    /**
     * Get the console command arguments.
     *
     * Used to remove the argument `name` defined in parent.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get stub file location for the model.
     *
     * @param string $file
     *
     * @return string
     */
    public function getStub($file = 'class.stub')
    {
        return __DIR__ . '/stubs/' . $file;
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     * @throws \Exception
     */
    public function handle()
    {
        // Prerequisites for the command to work.
        $this->prerequisites();

        ini_set('memory_limit', '512M');

        // Connections
        $connections = $this->getConnectionNames();

        if (count($connections) === 0) {
            $this->line(
                'No default connections specify. ' .
                'Please, set `bulk.connections` to `null` in your config file to run all configured connections, or ' .
                'specify one or more connections to run with the option `--connection`.'
            );
        } else {
            foreach ($connections as $connection) {
                $this->runConnection(DB::connection($connection));
            }
        }

        return null;
    }

    /**
     * Connections to be run.
     *
     * @return string[]
     */
    protected function getConnectionNames()
    {
        $connections = $this->option('connection');

        // Run all connections.
        // The input sets the option connection as an array, even when the value is null, so we need to double check.
        if (null === $connections || (0 === count($connections) && null === config('model-base.bulk.connections'))) {
            return array_keys(config('database.connections', []));
        }

        return $connections;
    }

    /**
     * @param \Illuminate\Database\Connection $connection
     *
     * @throws \Exception
     */
    protected function runConnection(Connection $connection)
    {
        $this->output->title('Bulk Model Base generation for ' . $connection->getName());

        // Utils
        $this->util = new Util($connection, $this);

        $this->loadTables();

        $bases = [];
        $models = [];
        foreach ($this->tables as $tableName) {
            $this->section($tableName);
            $this->util->make($tableName, $basePath, $modelPath);

            if ($basePath !== null) {
                $bases[] = $basePath;
            }

            if ($modelPath !== null) {
                $models[] = $modelPath;
            }
        }

        $this->showExtraBasesModels($bases);
        $this->showExtraModels($models);
    }

    /**
     * Verify that the app accomplish the pre-requisites.
     */
    protected function prerequisites()
    {
        if (!interface_exists('Doctrine\DBAL\Driver')) {
            $this->error($this->name . ' requires Doctrine DBAL; install "doctrine/dbal".');
            die();
        }
    }

    /**
     * Load tables to be used.
     */
    protected function loadTables()
    {
        $schemaUtil = $this->util->schemaUtil();

        // Exceptions
        $except = $schemaUtil->getTableExceptions();
        $this->line('Except: ' . implode(', ', $except));
        $this->output->newLine();

        // Load tables
        $this->tables = $schemaUtil->getTableNames($except);
    }

    /**
     * Write a string as section output.
     *
     * @param  string $string
     *
     * @return void
     */
    public function section($string)
    {
        /** @var \Illuminate\Console\OutputStyle $output */
        $output = $this->getOutput();
        if ($output->isVerbose()) {
            $output->section($string);
        } else {
            $output->writeln($string . ':');
        }
    }

    /**
     * @param string[] $files
     */
    protected function showExtraBasesModels($files)
    {
        return $this->showExtraFiles($this->getModelsBasesDirectory(), $files);
    }

    /**
     * @param string[] $files
     */
    protected function showExtraModels($files)
    {
        return $this->showExtraFiles($this->getModelsDirectory(), $files);
    }

    /**
     * @param string   $path
     * @param string[] $files
     */
    public function showExtraFiles($path, $files)
    {
        /** @var \Illuminate\Filesystem\Filesystem $app */
        $file = \Illuminate\Container\Container::getInstance()->make('files');

        if (!is_dir($path)) {
            return;
        }

        $extra = [];
        foreach ($file->allFiles($path) as $file) {
            if (!in_array($file, $files)) {
                $extra[] = $file;
            }
        }

        if (count($extra) > 0) {
            $this->line('There are ' . count($extra) . ' files unexpected in ' . $path . ':');
            foreach ($extra as $file) {
                $this->line('> ' . $file);
            }
        }
    }

    /**
     * Get the destination namespace directory path for the models bases.
     *
     * @return string
     */
    protected function getModelsBasesDirectory()
    {
        return $this->getNamespaceDirectory($this->util->config()->get('namespace'));
    }

    /**
     * Get the destination namespace directory path for the models bases.
     *
     * @return string
     */
    protected function getModelsDirectory()
    {
        return $this->getNamespaceDirectory($this->util->config()->get('model.namespace'));
    }

    /**
     * Get the destination namespace directory path for the models bases.
     *
     * @param string $namespace
     *
     * @return string
     */
    protected function getNamespaceDirectory($namespace)
    {
        /** @var \Laravel\Lumen\Application|\Illuminate\Foundation\Application $app */
        $app = \Illuminate\Container\Container::getInstance()->make('app');

        $name = str_replace($app->getNamespace(), '', $namespace);

        return $app->path() . '/' . str_replace('\\', '/', $name);
    }
}
