<?php

abstract class TestCase extends Orchestra\Testbench\TestCase
{
    /**
     * Default connection name for the tests.
     */
    const TEST_CONNECTION = 'testing';

    /**
     * If True, the default migration will be applied.
     */
    const DEFAULT_MIGRATION = true;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     */
    public function setUp()
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

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        if (static::DEFAULT_MIGRATION) {
            $this->migrateDown();
        }

        parent::tearDown();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [\Triun\ModelBase\ModelBaseServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', static::TEST_CONNECTION);
        $app['config']->set('database.connections.'.static::TEST_CONNECTION, $this->getDefaultDatabaseConfig());
    }

    /**
     * Get application timezone.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return string|null
     */
    protected function getApplicationTimezone($app)
    {
        return 'Asia/Kuala_Lumpur';
    }

    /**
     * Retrieve the default database configuration.
     *
     * @return array
     * @throws Exception
     */
    private function getDefaultDatabaseConfig()
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
                    'driver' => 'mysql',
                    'host' => env('DB_TEST_HOST', '127.0.0.1'),
                    'port' => env('DB_TEST_PORT', '3306'),
                    'database' => env('DB_TEST_DATABASE', 'testing'),
                    'username' => env('DB_TEST_USERNAME', 'testing'),
                    'password' => env('DB_TEST_PASSWORD', ''),
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix' => '',
                    'strict' => true,
                    'engine' => null,
                ];
                break;
            case 'pgsql':
                return [
                    'driver' => 'pgsql',
                    'host' => env('DB_TEST_HOST', '127.0.0.1'),
                    'port' => env('DB_TEST_PORT', '5432'),
                    'database' => env('DB_TEST_DATABASE', 'testing'),
                    'username' => env('DB_TEST_USERNAME', 'testing'),
                    'password' => env('DB_TEST_PASSWORD', ''),
                    'charset' => 'utf8',
                    'prefix' => '',
                    'schema' => 'public',
                    'sslmode' => 'prefer',
                ];
                break;
            case null:
                throw new Exception('DB_TEST_DRIVER not defined.');
            default:
                throw new Exception('DB_TEST_DRIVER '.env('DB_TEST_DRIVER').' does\'t exists.');
        }
    }

    /**
     * Migrate up
     */
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

    /**
     * Migrate down
     */
    protected function migrateDown()
    {
        Schema::drop('users');
        Schema::drop('posts');
    }

    /**
     * Retrieve a connection instance.
     *
     * @param string $connection
     *
     * @return \Illuminate\Database\Connection
     */
    protected function getConnection($connection = 'testing')
    {
        return DB::connection($connection);
    }

    /**
     * @param string $connection
     *
     * @return \Triun\ModelBase\ModelBaseConfig
     */
    protected function getConfig($connection = 'testing')
    {
        return new \Triun\ModelBase\ModelBaseConfig($this->getConnection($connection));
    }

    /**
     * @param string $connection
     *
     * @return \Triun\ModelBase\Utils\SchemaUtil
     */
    protected function getSchemaUtil($connection = 'testing')
    {
        return new \Triun\ModelBase\Utils\SchemaUtil($this->getConnection($connection), $this->getConfig($connection));
    }
}
