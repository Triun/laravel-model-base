<?php

namespace Triun\ModelBase\Console;

use Throwable;
use Triun\ModelBase\Util;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
        $this->prerequisites();

        $memory_limit = $this->option('memory_limit');
        if (null !== $memory_limit) {
            ini_set('memory_limit', $memory_limit);
        }
        $this->output->writeln(sprintf('memory_limit: %s', ini_get('memory_limit')));

        $tableName  = $this->argument('table');
        $connection = app('db')->connection($this->option('connection'));

        $util = new Util($connection, $this);

        $util->make($tableName);
    }

    protected function prerequisites(): void
    {
        if (!interface_exists('Doctrine\DBAL\Driver')) {
            $this->error($this->name . ' requires Doctrine DBAL; install "doctrine/dbal".');
            die();
        }
    }

    protected function getOptions(): array
    {
        return array_merge(
            parent::getOptions(),
            [
                // ['force', "f", InputOption::VALUE_NONE, 'Force override'],
                // ['keep', "k", InputOption::VALUE_NONE, 'Keep existent. No override'],
                ['connection', 'c', InputOption::VALUE_OPTIONAL, 'The connection we want to use'],
                [
                    'memory_limit',
                    'm',
                    InputOption::VALUE_REQUIRED,
                    'PHP ini memory_limit, sets the maximum amount of memory that a script is allowed to allocate',
                    config('model-base.bulk.memory_limit', null),
                ],
            ],
        );
    }

    protected function getArguments(): array
    {
        return [
            ['table', InputArgument::REQUIRED, 'The name of the table'],
        ];
    }
}
