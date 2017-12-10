<?php

namespace Triun\ModelBase\Lib;

use App;
use Exception;
use File;
use ReflectionProperty;
use RuntimeException;
use InvalidArgumentException;
use Triun\ModelBase\Definitions\Table;
use Triun\ModelBase\Utils\SkeletonUtil;
use Triun\ModelBase\Definitions\Skeleton;

/**
 * Class ModifierBase
 *
 * @package Triun\ModelBase\Lib
 */
abstract class ModifierBase
{
    /**
     * @var string[]
     */
    protected static $addons = [];

    /**
     * @var \Triun\ModelBase\Utils\SkeletonUtil
     */
    private $util;

    /**
     * @var \Doctrine\DBAL\Schema\Table
     */
    private $table;

    /**
     * @param  \Triun\ModelBase\Utils\SkeletonUtil $util
     * @param  \Triun\ModelBase\Definitions\Table  $table
     */
    public function __construct(SkeletonUtil $util, Table $table)
    {
        $this->util = $util;
        $this->table = $table;
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
    abstract public function apply(Skeleton $skeleton);

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
     * @return \Triun\ModelBase\ModelBaseConfig|mixed
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
     * @param string $path
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getFile(string $path)
    {
        return File::get($path);
    }

    /**
     * @param string $class
     *
     * @return string
     * @throws \Exception
     */
    public function getAddOn($class)
    {
        // Load AddOns cache
        if (array_key_exists($class, static::$addons)) {
            return static::$addons[$class];
        }

        $fromPath = $this->getClassNamePath($class);
        echo "$fromPath" . PHP_EOL;

        if (!File::exists($fromPath)) {
            throw new RuntimeException("The addon class $class doesn't exists.");
        }

        // If we don't want to save it, we can use the original
        // But it will be missing in prod if the library is in dev.
        if (!$this->config('addons.save')) {
            static::$addons[$class] = $class;

            return $class;
        }

        $newClass = $this->config()->getAddOnClassName($class);

        if (in_array($newClass, static::$addons)) {
            throw new InvalidArgumentException(sprintf(
                'There is already one addon named as %s. ' .
                'You are attempting to use the addon %s, but %s is already using this name.',
                $newClass,
                $class,
                array_flip(static::$addons)[$newClass]
            ));
        }

        if (!File::exists($newClass) || $this->config('addons.override')) {
            $toPath = $this->getAppClassNamePath($newClass);
            echo "$fromPath -> $toPath" . PHP_EOL;

            $content = File::get($fromPath);

            // Substitute class name in file content
            $content = str_replace(
                [class_basename($class), $this->getNamespace($class)],
                [class_basename($newClass), $this->getNamespace($newClass)],
                $content
            );

            if (!File::isDirectory(dirname($toPath))) {
                File::makeDirectory(dirname($toPath), 0777, true, true);
            }

            File::put($toPath, $content);
        }

        static::$addons[$class] = $newClass;

        return $newClass;
    }

    /**
     * Get the full namespace name.
     *
     * @param string $className
     *
     * @return string
     */
    protected function getNamespace(string $className)
    {
        return trim(implode('\\', array_slice(explode('\\', $className), 0, -1)), '\\');
    }

    /**
     * Get the destination class path.
     *
     * @param string $className
     *
     * @return string
     * @throws Exception
     */
    protected function getAppClassNamePath($className)
    {
        if (empty($className)) {
            throw new RuntimeException('Class name is empty');
        }

        $name = str_replace(App::getNamespace(), '', $className);

        return App::path() . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
    }

    /**
     * Get the destination class path.
     *
     * @param string $className
     *
     * @return string
     * @throws Exception
     */
    protected function getClassNamePath($className)
    {
        if (empty($className)) {
            throw new RuntimeException('Class name is empty');
        }

        /** @var \Composer\Autoload\ClassLoader $loader */
        $loader = require App::basePath() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR. 'autoload.php';

        return realpath($loader->findFile($className));
    }

    /**
     * Set constant value.
     *
     * @param  Skeleton $skeleton
     * @param  string   $name
     * @param  mixed    $value
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
     * @param  Skeleton   $skeleton
     * @param  string     $name
     * @param  mixed      $value
     * @param int         $modifiers_id
     * @param string|null $docComment
     *
     * @return $this
     */
    protected function setProperty(
        $skeleton,
        $name,
        $value,
        $modifiers_id = ReflectionProperty::IS_PUBLIC,
        $docComment = null
    ) {
        $this->util->setProperty($skeleton, $name, $value, $modifiers_id, $docComment);

        return $this;
    }

    /**
     * Set method value.
     *
     * @param  Skeleton $skeleton
     * @param  string   $name
     * @param  mixed    $value
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
     * @param string|string[] $rules
     * @param string          $value
     * @param bool            $case_sensitive
     *
     * @return bool
     */
    public function match($rules, $value, $case_sensitive = false)
    {
        return $this->util->match($rules, $value, $case_sensitive);
    }
}
