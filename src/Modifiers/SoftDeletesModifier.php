<?php

namespace Triun\ModelBase\Modifiers;

use Doctrine\DBAL\Types\Type;
use Triun\ModelBase\Lib\ModifierBase;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Definitions\Property;
use Triun\ModelBase\Definitions\PhpDocTag;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SoftDeletesModifier
 *
 * @package Triun\ModelBase\Modifiers
 *
 * @see     https://laravel.com/docs/5.3/eloquent#soft-deleting
 */
class SoftDeletesModifier extends ModifierBase
{
    /**
     * Constant name.
     */
    const NAME = 'DELETED_AT';

    /**
     * Deleted default column name value.
     */
    const DEFAULT_VALUE = 'deleted_at';

    /**
     * Scopes added by soft deletes that should be also added to PhpDoc.
     *
     * @var array
     */
    protected $scopes = [
        // Cannot make non static method Model->forceDelete() static
        // 'forceDelete',
        'restore',
        'withTrashed',
        'withoutTrashed',
        'onlyTrashed',
    ];

    /**
     * Apply the modifications of the class.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     *
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function apply(Skeleton $skeleton)
    {
        // Check if softDeletes is enabled in the config file
        if ($this->config('softDeletes', true) !== true) {
            return;
        }

        // Load the name, if exists.
        $columnName = $this->findDelete($skeleton);

        if ($columnName !== null) {
            // Add trait
            $skeleton->addTrait(SoftDeletes::class);
            // Set constant value
            $this->setConstant($skeleton, static::NAME, $columnName);
            // Add to dates array, if not exists.
            $this->addToDates($columnName, $skeleton->property('dates'));
            $this->addPHPDoc($skeleton);
        }
    }

    /**
     * Generate a timestamp in the skeleton with the replace values given by $field.
     *
     * @param Skeleton $skeleton
     *
     * @return mixed|null
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function findDelete(Skeleton $skeleton)
    {
        // Force use

        $force = $this->config("DELETED_AT.force");

        if (is_array($force)) {
            foreach ($force as $name) {
                if ($this->isValidColumn($name)) {
                    return $name;
                }
            }
        }

        // Find default

        $name = static::DEFAULT_VALUE;

        if ($this->isValidColumn($name)) {
            return $name;
        }

        // Alternative

        $alternative = $this->config("DELETED_AT.alternative");

        if (is_array($alternative)) {
            foreach ($alternative as $name) {
                if ($this->isValidColumn($name)) {
                    return $name;
                }
            }
        }

        return null;
    }

    /**
     * Whether the column could be a boolean deleted column or not.
     *
     * @param $columnName
     *
     * @return bool
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function isValidColumn($columnName)
    {
        return $this->hasColumn($columnName) && $this->columnIsTimestamp($columnName);
    }

    /**
     * Check if the column exists.
     *
     * @param $columnName
     *
     * @return bool
     */
    protected function hasColumn($columnName)
    {
        return $this->table()->hasColumn($columnName);
    }

    /**
     * Check if the column is boolean.
     *
     * @param $columnName
     *
     * @return bool
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function columnIsTimestamp($columnName)
    {
        // echo '+ deleted_at is '.$this->table()->getColumn($columnName)->getType()->getName().PHP_EOL;
        return $this->table()->getColumn($columnName)->getType()->getName() === Type::DATETIME;
    }

    /**
     * @param string                                $name
     * @param \Triun\ModelBase\Definitions\Property $dates
     */
    protected function addToDates($name, Property $dates)
    {
        // Add to dates array
        if (array_search($name, $dates->value) === false) {
            $dates->value[] = $name;
        }
    }

    /**
     * @param Skeleton $skeleton
     */
    protected function addPHPDoc(Skeleton $skeleton)
    {
        foreach ($this->scopes as $method) {
            $skeleton->addPhpDocTag(new PhpDocTag(
                "{$method}()",
                'method',
                'static \\Illuminate\\Database\\Query\\Builder|\DummyNamespace\DummyClass'
            ));
        }
    }
}
