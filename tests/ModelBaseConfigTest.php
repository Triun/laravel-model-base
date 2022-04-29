<?php

namespace Tests;

use Illuminate\Support\Facades\Config;

class ModelBaseConfigTest extends TestCase
{
    /**
     * This tests don't require migration.
     */
    const DEFAULT_MIGRATION = false;

    /**
     * An attribute name that we know that exists.
     *
     * @var string
     */
    protected string $exists = 'namespace';

    /**
     * An attribute name that we know that doesn't exists.
     *
     * @var string
     */
    protected string $notExists = 'some_not_existent_key';

    /**
     * Test static::getConfig()
     *
     * @test
     */
    public function model_base_config_loaded()
    {
        $laravelConfig = Config::get('model-base');
        //$this->assertNotNull(\Triun\ModelBase\ModelBaseConfig::class, $laravelConfig); // must be of the type string
        $this->assertArrayHasKey($this->exists, $laravelConfig);
    }

    /**
     * Test has()
     *
     * @see \Triun\ModelBase\ModelBaseConfig::has()
     *
     * @test
     */
    public function it_checks_if_an_attribute_exist()
    {
        $config = $this->getConfig();
        $this->assertFalse($config->has($this->notExists));
        $this->assertTrue($config->has($this->exists));
    }

    /**
     * Test get()
     *
     * @see \Triun\ModelBase\ModelBaseConfig::get()
     *
     * @test
     */
    public function it_loads_null_if_key_do_not_exists()
    {
        $config = $this->getConfig();
        $this->assertNull($config->get($this->notExists));
    }

    /**
     * Test get()
     *
     * @see \Triun\ModelBase\ModelBaseConfig::get()
     *
     * @test
     */
    public function it_loads_laravel_configuration()
    {
        $laravelConfig = Config::get('model-base');
        $config        = $this->getConfig();
        $this->assertInstanceOf(\Triun\ModelBase\ModelBaseConfig::class, $config);
        $this->assertEquals($laravelConfig[$this->exists], $config->get($this->exists));
    }

    /**
     * Test get()
     *
     * @see \Triun\ModelBase\ModelBaseConfig::get()
     *
     * @test
     */
//    public function it_can_specify_explicit_configuration_depending_of_the_connection_or_driver()
//    {
//    }

    /**
     * Test match()
     *
     * @see  \Triun\ModelBase\ModelBaseConfig::match()
     *
     * fnmatch separated by |
     * @link http://php.net/fnmatch
     * @link http://www.linfo.org/wildcard.html
     * '*gr[ae]y' is gray and grey
     * 'gray|grey' is also gray and grey
     * '*At|*_at finish in 'At' or '_at'
     *
     * @test
     */
    public function it_do_matches()
    {
        $config = $this->getConfig();

        // Single match
        $this->assertTrue($config->match('hello', 'hello'));

        // Case sensitive.
        $this->assertTrue($config->match('hello', 'Hello')); // Default false.
        $this->assertFalse($config->match('hello', 'Hello', true));
        $this->assertTrue($config->match('hello', 'Hello', false));

        // Options |
        $this->assertTrue($config->match('gray|grey', 'gray'));
        $this->assertTrue($config->match('gray|grey', 'grey'));
        $this->assertTrue($config->match('gray|grey', 'Grey'));
        $this->assertFalse($config->match('gray|grey', 'Grey', true));
        $this->assertFalse($config->match('gray|grey', 'grey_'));
        $this->assertFalse($config->match('gray|grey', '_grey'));

        // Star Wildcard Suffix
        $this->assertTrue($config->match('*suffix', 'suffix'));
        $this->assertTrue($config->match('*suffix', '_suffix'));
        $this->assertFalse($config->match('*suffix', 'suffix_'));
        $this->assertFalse($config->match('*suffix', '_suffix_'));

        // Star Wildcard Prefix
        $this->assertTrue($config->match('prefix*', 'prefix'));
        $this->assertFalse($config->match('prefix*', '_prefix'));
        $this->assertTrue($config->match('prefix*', 'prefix_'));
        $this->assertFalse($config->match('prefix*', '_prefix_'));

        // Star Wildcard Middle
        $this->assertTrue($config->match('*middle*', 'middle'));
        $this->assertTrue($config->match('*middle*', '_middle'));
        $this->assertTrue($config->match('*middle*', 'middle_'));
        $this->assertTrue($config->match('*middle*', '_middle_'));

        // Question Mark Wildcard
        $this->assertFalse($config->match('word?', 'word'));
        $this->assertTrue($config->match('word?', 'word_'));
        $this->assertFalse($config->match('?word?', 'word'));
        $this->assertFalse($config->match('?word?', 'word_'));
        $this->assertFalse($config->match('?word?', '_word'));
        $this->assertTrue($config->match('?word?', '_word_'));
        $this->assertFalse($config->match('word??', 'word'));
        $this->assertFalse($config->match('word??', 'word_'));
        $this->assertTrue($config->match('word??', 'word__'));

        // Square Brackets Wildcard [ae]
        $this->assertTrue($config->match('gr[ae]y', 'grey'));
        $this->assertTrue($config->match('gr[ae]y', 'Grey'));
        $this->assertFalse($config->match('gr[ae]y', 'Grey', true));
        $this->assertFalse($config->match('gr[ae]y', 'grey_'));
        $this->assertFalse($config->match('gr[ae]y', '_grey'));

        // Square Brackets Wildcard [0-9]
        $this->assertFalse($config->match('word_[0-9]', 'word__'));
        $this->assertTrue($config->match('word_[0-9]', 'word_1'));
        $this->assertFalse($config->match('word_[0-9]', 'word_A'));
        $this->assertFalse($config->match('word_[0-9]', 'word_a'));

        // Square Brackets Wildcard [a-z] & [A-Z]
        $this->assertFalse($config->match('word_[a-z]', 'word__'));
        $this->assertFalse($config->match('word_[a-z]', 'word_1'));
        $this->assertTrue($config->match('word_[a-z]', 'word_A'));
        $this->assertTrue($config->match('word_[a-z]', 'word_a'));
        $this->assertFalse($config->match('word_[a-z]', 'word_A', true));
        $this->assertTrue($config->match('word_[a-z]', 'word_a', true));
        $this->assertTrue($config->match('word_[A-Z]', 'word_A', true));
        $this->assertFalse($config->match('word_[A-Z]', 'word_a', true));

        // Square Brackets Wildcard [a-cst]
        $this->assertTrue($config->match('word_[a-cst]', 'word_b'));
        $this->assertTrue($config->match('word_[a-cst]', 'word_s'));
        $this->assertTrue($config->match('word_[a-cst]', 'word_t'));
        $this->assertFalse($config->match('word_[a-cst]', 'word_d'));

        // Square Brackets Wildcard [a-cx-z]
        $this->assertTrue($config->match('word_[a-cx-z]', 'word_b'));
        $this->assertTrue($config->match('word_[a-cx-z]', 'word_y'));
        $this->assertFalse($config->match('word_[a-cst]', 'word_d'));

        // Square Brackets Wildcard [0-9][0-9][0-9]
        $this->assertTrue($config->match('word_[0-9][0-9][0-9]', 'word_123'));
        $this->assertFalse($config->match('word_[0-9][0-9][0-9]', 'word_12'));

        // Mix
        $this->assertTrue($config->match('*At|*_at', 'deleted_at'));
        $this->assertTrue($config->match('*At|*_at', 'deletedAt'));
        $this->assertFalse($config->match('*At|*_at', 'deletedAtTime'));
        $this->assertTrue($config->match('*At|*_at', 'At'));
    }

    /**
     * Test modifiers()
     *
     * @see \Triun\ModelBase\ModelBaseConfig::modifiers()
     *
     * @test
     */
    public function it_can_retrieve_the_modifiers()
    {
        $connection  = $this->getTestingConnection();
        $unprotected = new ConfigUnprotected($connection);
        $default     = $unprotected->getProperty('modifiers');

        $config = $this->getConfig();
        $this->assertEquals($default, $config->modifiers());

        // If we add more modifiers to the configuration...
        $newModifiers = [
            'ExampleModifier1',
            'ExampleModifier2',
        ];
        config(['model-base.modifiers' => $newModifiers]);
        $config = $this->getConfig();

        $this->assertNotEquals($default, $config->modifiers());
        $this->assertContains($newModifiers[0], $config->modifiers());
        $this->assertContains($newModifiers[1], $config->modifiers());
    }

    /**
     * Test getClassName()
     *
     * @see \Triun\ModelBase\ModelBaseConfig::getClassName()
     *
     * @test
     */
    public function it_can_generate_a_class_name()
    {
        $connection  = $this->getTestingConnection();
        $unprotected = new ConfigUnprotected($connection);

        // To singular
        $this->assertEquals('App\\Models\\User', $unprotected->run_getClassName('user', 'App\\Models', '', '', [], []));
        $this->assertEquals(
            'App\\Models\\User',
            $unprotected->run_getClassName('users', 'App\\Models', '', '', [], [])
        );

        // Prefix and suffix
        $this->assertEquals(
            'App\\Models\\PrefixUser',
            $unprotected->run_getClassName('users', 'App\\Models', 'Prefix', '', [], [])
        );
        $this->assertEquals(
            'App\\Models\\UserSuffix',
            $unprotected->run_getClassName('users', 'App\\Models', '', 'Suffix', [], [])
        );
        $this->assertEquals(
            'App\\Models\\PrefixUserSuffix',
            $unprotected->run_getClassName('users', 'App\\Models', 'Prefix', 'Suffix', [], [])
        );

        // Renames
        $this->assertEquals(
            'App\\Models\\Customer',
            $unprotected->run_getClassName('users', 'App\\Models', '', '', ['users' => 'Customer'], [])
        );
        $this->assertEquals(
            'App\\Models\\Customer',
            $unprotected->run_getClassName('users', 'App\\Models', '', '', ['users' => '  customer  '], [])
        );
        $this->assertNotEquals(
            'App\\Models\\Customer',
            $unprotected->run_getClassName('users', 'App\\Models', '', '', ['users' => 'customers'], [])
        );

        // Prefixes
        $this->assertEquals(
            'App\\Models\\TblUser',
            $unprotected->run_getClassName('tbl_users', 'App\\Models', '', '', [], [])
        );
        $this->assertEquals(
            'App\\Models\\TblUser',
            $unprotected->run_getClassName('tbl_users', 'App\\Models', '', '', [], [null])
        );
        $this->assertEquals(
            'App\\Models\\User',
            $unprotected->run_getClassName('tbl_users', 'App\\Models', '', '', [], ['tbl_'])
        );
        $this->assertEquals(
            'App\\Models\\User',
            $unprotected->run_getClassName('tbl_users', 'App\\Models', '', '', [], ['tbl_', null])
        );
        $this->assertEquals(
            'App\\Models\\User',
            $unprotected->run_getClassName('tbl_users', 'App\\Models', '', '', [], [null, 'tbl_'])
        );
        $this->assertEquals(
            'App\\Models\\TblUser',
            $unprotected->run_getClassName('tbl_users', 'App\\Models', '', '', [], ['t_'])
        );
        $this->assertEquals(
            'App\\Models\\Customer',
            $unprotected->run_getClassName('tbl_users', 'App\\Models', '', '', ['tbl_users' => 'Customer'], ['tbl_'])
        );
    }

    /**
     * Test getBaseClassName()
     *
     * @see \Triun\ModelBase\ModelBaseConfig::getBaseClassName()
     *
     * @test
     */
    public function it_can_generate_a_base_class_name()
    {
        $config = $this->getConfig();

        $this->assertEquals('App\\Models\\Bases\\Testing\\UserBase', $config->getBaseClassName('users'));
    }

    /**
     * Test getModelClassName()
     *
     * @see \Triun\ModelBase\ModelBaseConfig::getModelClassName()
     *
     * @test
     */
    public function it_can_generate_a_model_class_name()
    {
        $config = $this->getConfig();

        $this->assertEquals('App\\Models\\Testing\\User', $config->getModelClassName('users'));
    }
}

class ConfigUnprotected extends \Triun\ModelBase\ModelBaseConfig
{
    public function getProperty($key)
    {
        return $this->$key;
    }

    /**
     * @param string   $tableName
     * @param string   $namespace
     * @param string   $prefix
     * @param string   $suffix
     * @param string[] $tableRenames
     * @param string[] $tablePrefixes
     *
     * @return string
     */
    public function run_getClassName(
        string $tableName,
        string $namespace,
        string $prefix,
        string $suffix,
        array $tableRenames,
        array $tablePrefixes
    ): string {
        return $this->getClassName($tableName, $namespace, $prefix, $suffix, $tableRenames, $tablePrefixes);
    }
}
