<?php

namespace Triun\ModelBase\Modifiers;

use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Triun\ModelBase\Lib\ModifierBase;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Definitions\Property;
use Triun\ModelBase\Definitions\PhpDocTag;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @see https://laravel.com/docs/9.x/eloquent#soft-deleting
 */
class SoftDeletesModifier extends ModifierBase
{
    const NAME = 'DELETED_AT';
    const DEFAULT_VALUE = 'deleted_at';

    /**
     * Scopes added by soft deletes that should be also added to PhpDoc.
     */
    protected array $scopes = [
        // Cannot make non static method Model->forceDelete() static
        // 'forceDelete',
        'restore',
        'withTrashed',
        'withoutTrashed',
        'onlyTrashed',
    ];

    /**
     * @throws SchemaException
     */
    public function apply(Skeleton $skeleton): void
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
     * Generate a timestamp in the skeleton with the replacement values given by $field.
     *
     * @throws SchemaException
     */
    protected function findDelete(Skeleton $skeleton): mixed
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
     * @throws SchemaException
     */
    protected function isValidColumn(string $columnName): bool
    {
        return $this->hasColumn($columnName) && $this->columnIsTimestamp($columnName);
    }

    protected function hasColumn(string $columnName): bool
    {
        return $this->table()->hasColumn($columnName);
    }

    /**
     * @throws SchemaException
     */
    protected function columnIsTimestamp($columnName): bool
    {
        // echo '+ deleted_at is '.$this->table()->getColumn($columnName)->getType()->getName().PHP_EOL;
        return $this->table()->getColumn($columnName)->getType()->getName() === Types::DATETIME_IMMUTABLE;
    }

    protected function addToDates(string $name, Property $dates): void
    {
        // Add to dates array
        if (!in_array($name, $dates->value)) {
            $dates->value[] = $name;
        }
    }

    /**
     * @param Skeleton $skeleton
     */
    protected function addPHPDoc(Skeleton $skeleton): void
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
