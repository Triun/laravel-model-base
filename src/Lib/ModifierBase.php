<?php

namespace Triun\ModelBase\Lib;

use Exception;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionProperty;
use RuntimeException;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Definitions\Table;
use Triun\ModelBase\ModelBaseConfig;
use Triun\ModelBase\Utils\SkeletonUtil;

abstract class ModifierBase
{
    /**
     * @var string[]
     */
    protected static array $addons = [];

    private SkeletonUtil $util;

    private Table|\Doctrine\DBAL\Schema\Table $table;

    /**
     * @param \Triun\ModelBase\Utils\SkeletonUtil $util
     * @param \Triun\ModelBase\Definitions\Table  $table
     */
    public function __construct(SkeletonUtil $util, Table $table)
    {
        $this->util  = $util;
        $this->table = $table;
    }

    /**
     * Load before executing...
     */
    public static function boot(): void
    {
        // ...
    }

    /**
     * Apply the modifications of the class.
     */
    abstract public function apply(Skeleton $skeleton);

    protected function util(): SkeletonUtil
    {
        return $this->util;
    }

    /**
     * @return ModelBaseConfig|mixed
     */
    protected function config(?string $key = null, mixed $default = null): mixed
    {
        return $this->util->config($key, $default);
    }

    protected function connection(): Connection
    {
        return $this->util->connection();
    }

    protected function table(): \Doctrine\DBAL\Schema\Table|Table
    {
        return $this->table;
    }

    /**
     * Get stub file location.
     */
    public function getStub(string $file): string
    {
        return __DIR__ . '/../../resources/stubs/' . $file;
    }

    /**
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     */
    public function getFile(string $path): string
    {
        return app('file')->get($path);
    }

    /**
     * @throws Exception
     */
    public function getAddOn(string $class): string
    {
        // Load AddOns cache
        if (array_key_exists($class, static::$addons)) {
            return static::$addons[$class];
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

        $this->saveAddOn($class, $newClass);

        static::$addons[$class] = $newClass;

        return $newClass;
    }

    /**
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     * @throws Exception
     */
    protected function saveAddOn(string $class, string $newClass): void
    {
        $fromPath = $this->getClassNamePath($class);

        if (!app('file')->exists($fromPath)) {
            throw new RuntimeException("The addon class $class doesn't exists.");
        }

        $toPath = $this->getAppClassNamePath($newClass);
        $exists = app('file')->exists($toPath);

        // If exists but we do not have override permissions
        if ($exists && !$this->config('addons.override')) {
            return;
        }

        // Substitute class name in file content
        $content = $this->getNewAddOnContent(app('file')->get($fromPath), $class, $newClass);

        // If exists, but the content is no changed
        if ($exists && $content === app('file')->get($toPath)) {
            return;
        }

        echo "Save Addon: $fromPath -> $toPath" . PHP_EOL;

        if (!app('file')->isDirectory(dirname($toPath))) {
            app('file')->makeDirectory(dirname($toPath), 0777, true, true);
        }

        app('file')->put($toPath, $content);
    }

    protected function getNewAddOnContent(string $content, string $class, string $newClass): string|array
    {
        return str_replace(
            [class_basename($class), $this->getNamespace($class)],
            [class_basename($newClass), $this->getNamespace($newClass)],
            $content
        );
    }

    /**
     * Get the full namespace name.
     */
    protected function getNamespace(string $className): string
    {
        return trim(implode('\\', array_slice(explode('\\', $className), 0, -1)), '\\');
    }

    /**
     * Get the destination class path.
     *
     * @throws Exception
     */
    protected function getAppClassNamePath(string $className): string
    {
        if (empty($className)) {
            throw new RuntimeException('Class name is empty');
        }

        $name = str_replace(app()->getNamespace(), '', $className);

        return app()->path() . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
    }

    /**
     * Get the destination class path.
     *
     * @throws Exception
     */
    protected function getClassNamePath(string $className): string
    {
        if (empty($className)) {
            throw new RuntimeException('Class name is empty');
        }

        /** @var \Composer\Autoload\ClassLoader $loader */
        $loader = require app()->basePath() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

        return realpath($loader->findFile($className));
    }

    public function setConstant(Skeleton $skeleton, string $name, mixed $value): static
    {
        $this->util->setConstant($skeleton, $name, $value);

        return $this;
    }

    protected function setProperty(
        Skeleton $skeleton,
        string $name,
        mixed $value,
        int $modifiers_id = ReflectionProperty::IS_PUBLIC,
        string $docComment = null
    ): static {
        $this->util->setProperty($skeleton, $name, $value, $modifiers_id, $docComment);

        return $this;
    }

    protected function setMethod(Skeleton $skeleton, string $name, mixed $value): static
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
    public function match(array|string $rules, string $value, bool $case_sensitive = false): bool
    {
        return $this->util->match($rules, $value, $case_sensitive);
    }
}
