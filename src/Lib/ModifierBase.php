<?php


namespace Triun\ModelBase\Lib;


use File;
use Triun\ModelBase\Definitions\Table;
use Triun\ModelBase\Utils\SkeletonUtil;
use Triun\ModelBase\Definitions\Skeleton;

abstract class ModifierBase
{
    /**
     * @var \Triun\ModelBase\Utils\SkeletonUtil
     */
    private $util;

    /**
     * @var \Doctrine\DBAL\Schema\Table
     */
    private $table;

    /**
     * @param  \Triun\ModelBase\Utils\SkeletonUtil   $util
     * @param  \Triun\ModelBase\Definitions\Table    $table
     */
    public function __construct(SkeletonUtil $util, Table $table)
    {
        $this->util     = $util;
        $this->table    = $table;
    }

    /**
     * Load before executing...
     */
    public static function boot()
    {
        // ...
    }

    /**
     * Apply the modifications of the class.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     */
    abstract function apply(Skeleton $skeleton);

    /**
     * @return \Triun\ModelBase\Utils\SkeletonUtil
     */
    protected function util()
    {
        return $this->util;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return \Triun\ModelBase\ModelBaseConfig
     */
    protected function config($key = null, $default = null)
    {
        return $this->util->config($key, $default);
    }

    /**
     * @return \Illuminate\Database\Connection
     */
    protected function connection()
    {
        return $this->util->connection();
    }

    /**
     * @return \Triun\ModelBase\Definitions\Table
     */
    protected function table()
    {
        return $this->table;
    }

    /**
     * Get stub file location.
     *
     * @param string $file
     *
     * @return string
     */
    public function getStub($file)
    {
        return __DIR__ . '/../../resources/stubs/' . $file;
    }

    /**
     * Return filesystem instance.
     *
     * @return string
     */
    public function getFile($path)
    {
        return File::get($path);
    }

    /**
     * Set constant value.
     *
     * @param  Skeleton $skeleton
     * @param  string $name
     * @param  mixed  $value
     *
     * @return $this
     */
    public function setConstant($skeleton, $name, $value)
    {
        $this->util->setConstant($skeleton, $name, $value);

        return $this;
    }

    /**
     * Set property value.
     *
     * @param  Skeleton $skeleton
     * @param  string $name
     * @param  mixed  $value
     *
     * @return $this
     */
    protected function setProperty($skeleton, $name, $value)
    {
        $this->util->setProperty($skeleton, $name, $value);

        return $this;
    }

    /**
     * Set method value.
     *
     * @param  Skeleton $skeleton
     * @param  string  $name
     * @param  mixed  $value
     *
     * @return $this
     */
    protected function setMethod($skeleton, $name, $value)
    {
        $this->util->setMethod($skeleton, $name, $value);

        return $this;
    }

    /**
     * fnmatch separated by |
     * http://php.net/fnmatch
     * '*gr[ae]y' is gray and grey
     * 'gray|grey' is also gray and grey
     * '*At|*_at finish in 'At' or '_at'
     *
     * @param string|string[]   $rules
     * @param string            $value
     * @param bool              $case_sensitive
     *
     * @return bool
     */
    public function match($rules, $value, $case_sensitive = false)
    {
        return $this->util->match($rules, $value, $case_sensitive);
    }
}