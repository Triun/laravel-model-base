<?php

namespace Tests;

use Exception;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Default connection name for the tests.
     */
    const TEST_CONNECTION = 'testing';

    /**
     * If True, the default migration will be applied.
     */
    const DEFAULT_MIGRATION = true;

    public function setUp(): void
    {
        parent::setUp();

        // It is supposed to be added by Orchestra, but it's not, so we add it manually.
        $provider = new \Triun\ModelBase\ModelBaseServiceProvider($this->app);
        $provider->boot();
//        dd($this->app['config']['model-base']);

        if (static::DEFAULT_MIGRATION) {
            $this->migrateUp();
        }
    }

    protected function tearDown(): void
    {
        if (static::DEFAULT_MIGRATION) {
            $this->migrateDown();
        }

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [\Triun\ModelBase\ModelBaseServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', static::TEST_CONNECTION);
        $app['config']->set('database.connections.' . static::TEST_CONNECTION, $this->getDefaultDatabaseConfig());
    }

    protected function getApplicationTimezone($app): ?string
    {
        return 'Asia/Kuala_Lumpur';
    }

    /**
     * @throws Exception
     */
    private function getDefaultDatabaseConfig(): array
    {
        switch (env('DB_TEST_DRIVER', null)) {
            case 'sqlite':
                return [
                    'driver'   => 'sqlite',
                    'database' => env('DB_TEST_DATABASE', ':memory:'), //database_path('database.sqlite')),
                    'prefix'   => '',
                ];
                break;
            case 'mysql':
                return [
                    'driver'    => 'mysql',
                    'host'      => env('DB_TEST_HOST', '127.0.0.1'),
                    'port'      => env('DB_TEST_PORT', '3306'),
                    'database'  => env('DB_TEST_DATABASE', 'testing'),
                    'username'  => env('DB_TEST_USERNAME', 'testing'),
                    'password'  => env('DB_TEST_PASSWORD', ''),
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                    'strict'    => true,
                    'engine'    => null,
                ];
                break;
            case 'pgsql':
                return [
                    'driver'   => 'pgsql',
                    'host'     => env('DB_TEST_HOST', '127.0.0.1'),
                    'port'     => env('DB_TEST_PORT', '5432'),
                    'database' => env('DB_TEST_DATABASE', 'testing'),
                    'username' => env('DB_TEST_USERNAME', 'testing'),
                    'password' => env('DB_TEST_PASSWORD', ''),
                    'charset'  => 'utf8',
                    'prefix'   => '',
                    'schema'   => 'public',
                    'sslmode'  => 'prefer',
                ];
                break;
            case null:
                throw new Exception('DB_TEST_DRIVER not defined.');
            default:
                throw new Exception('DB_TEST_DRIVER ' . env('DB_TEST_DRIVER') . ' does\'t exists.');
        }
    }

    protected function migrateUp()
    {
        Schema::create('users', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('posts', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('metadata');
            $table->timestamps();
        });
    }

    protected function migrateDown(): void
    {
        Schema::drop('users');
        Schema::drop('posts');
    }

    protected function getTestingConnection(string $connection = 'testing'): Connection
    {
        return $this->getConnection($connection);
    }

    protected function getConfig(string $connection = 'testing'): \Triun\ModelBase\ModelBaseConfig
    {
        return new \Triun\ModelBase\ModelBaseConfig($this->getTestingConnection($connection));
    }

    protected function getSchemaUtil(string $connection = 'testing'): \Triun\ModelBase\Utils\SchemaUtil
    {
        return new \Triun\ModelBase\Utils\SchemaUtil(
            $this->getTestingConnection($connection),
            $this->getConfig($connection)
        );
    }
}
