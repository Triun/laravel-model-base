<?php

namespace Triun\ModelBase\Console;

use DB;
use Triun\ModelBase\Util;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MakeCommand
 *
 * @package Triun\ModelBase\Console
 */
class MakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:model-base';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Eloquent Base model class';

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
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $this->prerequisites();

        $tableName = $this->argument('table');
        $connection = DB::connection($this->option('connection'));

        $util = new Util($connection, $this);

        $util->make($tableName);
    }

    protected function prerequisites()
    {
        if (!interface_exists('Doctrine\DBAL\Driver')) {
            $this->error($this->name . ' requires Doctrine DBAL; install "doctrine/dbal".');
            die();
        }
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
                // ['force', "f", InputOption::VALUE_NONE, 'Force override'],
                // ['keep', "k", InputOption::VALUE_NONE, 'Keep existent. No override'],
                ['connection', 'c', InputOption::VALUE_OPTIONAL, 'The connection we want to use'],
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
        return [
            ['table', InputArgument::REQUIRED, 'The name of the table'],
        ];
    }
}
