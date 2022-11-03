<?php

namespace Triun\ModelBase\Console;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;
use Throwable;
use Triun\ModelBase\Util;
use Illuminate\Database\Connection;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

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

    protected Util $util;

    /**
     * @var string[]
     */
    protected array $tables;

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

    protected function getArguments(): array
    {
        return [];
    }

    /**
     * Get stub file location for the model.
     */
    public function getStub(string $file = 'class.stub'): string
    {
        return __DIR__ . '/stubs/' . $file;
    }

    /**
     * @throws Throwable
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
                'specify one or more connections to run with the option `--connection`.',
            );
        } else {
            foreach ($connections as $connection) {
                $this->runConnection(app('db')->connection($connection));
            }
        }

        return null;
    }

    /**
     * Connections to be run.
     *
     * @return string[]
     */
    protected function getConnectionNames(): array
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
     * @param Connection $connection
     *
     * @throws Throwable
     */
    protected function runConnection(Connection $connection): void
    {
        $this->output->title('Bulk Model Base generation for ' . $connection->getName());

        // Utils
        $this->util = new Util($connection, $this);

        $this->loadTables();

        $bases  = [];
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
    protected function prerequisites(): void
    {
        if (!interface_exists('Doctrine\DBAL\Driver')) {
            $this->error($this->name . ' requires Doctrine DBAL; install "doctrine/dbal".');
            die();
        }
    }

    /**
     * Load tables to be used.
     */
    protected function loadTables(): void
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
     */
    public function section(string $string): void
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
     * @throws BindingResolutionException
     */
    protected function showExtraBasesModels(array $files): void
    {
        $this->showExtraFiles($this->getModelsBasesDirectory(), $files);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function showExtraModels(array $files): void
    {
        $this->showExtraFiles($this->getModelsDirectory(), $files);
    }

    /**
     * @param string   $path
     * @param string[] $files
     *
     * @throws BindingResolutionException
     */
    public function showExtraFiles(string $path, array $files): void
    {
        /** @var Filesystem $app */
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
     */
    protected function getModelsBasesDirectory(): string
    {
        return $this->getNamespaceDirectory($this->util->config()->get('namespace'));
    }

    /**
     * Get the destination namespace directory path for the models bases.
     */
    protected function getModelsDirectory(): string
    {
        return $this->getNamespaceDirectory($this->util->config()->get('model.namespace'));
    }

    /**
     * Get the destination namespace directory path for the models bases.
     */
    protected function getNamespaceDirectory(string $namespace): string
    {
        $app  = app();
        $name = str_replace($app->getNamespace(), '', $namespace);

        return $app->path() . '/' . str_replace('\\', '/', $name);
    }
}
