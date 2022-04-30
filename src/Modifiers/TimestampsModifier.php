<?php

namespace Triun\ModelBase\Modifiers;

use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Lib\ModifierBase;

class TimestampsModifier extends ModifierBase
{
    protected string $voidTimestamp_stub = 'timestamps/voidTimestamp.stub';

    /**
     * @throws SchemaException
     * @throws FileNotFoundException
     */
    public function apply(Skeleton $skeleton): void
    {
        $CREATED_AT = $this->generateTimestamp($skeleton, 'CREATED_AT');
        $UPDATED_AT = $this->generateTimestamp($skeleton, 'UPDATED_AT');

        // Set it if there is any
        $skeleton->property('timestamps')->setValue($this->hasColumn($CREATED_AT) || $this->hasColumn($UPDATED_AT));

        // Set the fields values
        $skeleton->constant('CREATED_AT')->setValue($CREATED_AT);
        $skeleton->constant('UPDATED_AT')->setValue($UPDATED_AT);

        // Create void mutators to avoid errors
        if ($skeleton->property('timestamps')->value === true) {
            $stub = $this->voidTimestamp();

            if (!$this->hasColumn($CREATED_AT)) {
                $skeleton->addMethod($this->util()->makeMethod('setCreatedAt', $stub, [
                    'dummyName'     => 'created',
                    'DummyName'     => 'Created',
                    'DUMMY_NAME_AT' => 'CREATED_AT',
                ]));
            }

            if (!$this->hasColumn($UPDATED_AT)) {
                $skeleton->addMethod($this->util()->makeMethod('setUpdatedAt', $stub, [
                    'dummyName'     => 'updated',
                    'DummyName'     => 'Updated',
                    'DUMMY_NAME_AT' => 'UPDATED_AT',
                ]));
            }
        }
    }

    /**
     * Generate a timestamp in the skeleton with the replace values given by $field.
     *
     * @throws SchemaException
     */
    protected function generateTimestamp(Skeleton $skeleton, string $field): mixed
    {
        $const = $skeleton->constant($field);

        // Force use

        $force = $this->config("timestamps.$field.force");

        if (is_array($force)) {
            foreach ($force as $name) {
                if ($this->hasColumn($name) && $this->columnIsTimestamp($name)) {
                    return $name;
                }
            }
        }

        // Find default

        $name = $const->default;

        if ($this->hasColumn($name) && $this->columnIsTimestamp($name)) {
            return $name;
        }

        // Alternative

        $alternative = $this->config("timestamps.$field.alternative");

        if (is_array($alternative)) {
            foreach ($alternative as $name) {
                if ($this->hasColumn($name) && $this->columnIsTimestamp($name)) {
                    return $name;
                }
            }
        }

        return null;
    }

    protected function hasColumn(?string $name): bool
    {
        return $this->table()->hasColumn($name);
    }

    /**
     * @throws SchemaException
     */
    protected function columnIsTimestamp(string $name): bool
    {
        return $this->table()->getColumn($name)->getType()->getName() === Types::DATETIME_MUTABLE;
    }

    /**
     * Retrieve the template.
     *
     * @throws FileNotFoundException
     */
    protected function voidTimestamp(): string
    {
        return $this->getFile($this->getStub($this->voidTimestamp_stub));
    }
}
