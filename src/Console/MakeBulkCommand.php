<?php


namespace Triun\ModelBase\Console;

use DB;
use App;
use Triun\ModelBase\Util;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MakeBulkCommand
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
     * Get stub file location for the model.
     *
     * @param string $file
     *
     * @return string
     */
    public function getStub($file = 'class.stub')
    {
        return __DIR__ . '/stubs/'.$file;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        // Prerequisites for the command to work.
        $this->prerequisites();

        // Connection
        $connection = DB::connection($this->option('connection'));
        $this->output->title('Bulk Model Base generation for '.$connection->getName());

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
        if (! interface_exists('Doctrine\DBAL\Driver')) {
            $this->error($this->name.' requires Doctrine DBAL; install "doctrine/dbal".');
            die();
        }
    }

    /**
     * Load tables to be used.
     */
    protected function loadTables()
    {
        $schemaUtil = $this->util->schema_util();

        // Exceptions
        $except = $schemaUtil->getTableExceptions();
        $this->line('Except: '.implode(', ', $except));
        $this->output->newLine();

        // Load tables
        $this->tables = $schemaUtil->getTableNames($except);
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
//                ['force', "f", InputOption::VALUE_NONE, 'Force override'],
//                ['keep', "k", InputOption::VALUE_NONE, 'Keep existent. No override'],
                ['connection', 'c', InputOption::VALUE_OPTIONAL, 'The connection we want to use'],
                //['except', null, InputOption::VALUE_OPTIONAL, 'The tables we want to exclude, as comma separated'],
            ]
        );
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Write a string as section output.
     *
     * @param  string  $string
     * @return void
     */
    public function section($string)
    {
        /** @var \Illuminate\Console\OutputStyle $output */
        $output = $this->getOutput();
        if ($output->isVerbose()) {
            $output->section($string);
        }
        else {
            $output->writeln($string.':');
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
     * @param string $path
     * @param string[] $files
     */
    public function showExtraFiles($path, $files)
    {
        $extra = [];
        foreach (\File::allFiles($path) as $file) {
            if (!in_array($file, $files)) {
                $extra[] = $file;
            }
        }

        if (count($extra) > 0) {
            $this->line('There are '.count($extra). ' files unexpected in '.$path.':');
            foreach ($extra as $file) {
                $this->line('> '.$file);
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
        $name = str_replace(App::getNamespace(), '', $namespace);

        return App::path().'/'.str_replace('\\', '/', $name);
    }
}
