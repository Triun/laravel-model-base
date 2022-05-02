<?php

namespace Tests;

use Illuminate\Support\Facades\Config;

class TestCaseTest extends TestCase
{

    /**
     * Test \Illuminate\Support\Facades\Config facade is usable.
     *
     * @test
     */
    public function config_facade_is_loaded()
    {
        $this->assertEquals('testing', Config::get('database.default'));
    }

    /**
     * Test config() helper is usable.
     *
     * @test
     */
    public function config_helper_is_loaded()
    {
        $this->assertEquals('testing', config('database.default'));
    }

    /**
     * Test running migration.
     *
     * @test
     */
//    public function running_migrations()
//    {
//        $users = \DB::table('users')->where('id', '=', 1)->first();
//        $this->assertEquals('hello@triun.gonzalom.com', $users->email);
//        $this->assertTrue(\Hash::check('123', $users->password));
//    }

    /**
     * Test static::getConnection()
     *
     * @test
     */
    public function it_gets_the_connection()
    {
        $connection = $this->getTestingConnection();
        $this->assertInstanceOf(\Illuminate\Database\Connection::class, $connection);
        $this->assertEquals(static::TEST_CONNECTION, $connection->getName());
        $this->assertEquals(env('DB_TEST_DRIVER'), $connection->getDriverName());
    }

    /**
     * Test static::getConfig()
     *
     * @test
     */
    public function it_gets_the_configuration_object()
    {
        $config = $this->getConfig();
        $this->assertInstanceOf(\Triun\ModelBase\ModelBaseConfig::class, $config);

//        dd(\Illuminate\Support\Facades\Config::get('model-base'));

//        $config_file = require realpath(__DIR__.'/../config/model-base.php');
//        $this->assertEquals($config_file['namespace'], $config->get('namespace'));
    }

    /**
     * Test static::getSchemaUtil()
     *
     * @test
     */
    public function it_gets_the_schema_util()
    {
        $schemaUtil = $this->getSchemaUtil();
        $this->assertInstanceOf(\Triun\ModelBase\Utils\SchemaUtil::class, $schemaUtil);
    }
}
