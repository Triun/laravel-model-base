<?php

namespace Triun\ModelBase\Modifiers;

use Doctrine\DBAL\Types\Type;
use Triun\ModelBase\Lib\ModifierBase;
use Triun\ModelBase\Definitions\Skeleton;

class TimestampsModifier extends ModifierBase
{
    /**
     * Stub template.
     *
     * @var string
     */
    protected $voidTimestamp_stub = 'timestamps/voidTimestamp.stub';

    /**
     * Apply the modifications of the class.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     */
    public function apply(Skeleton $skeleton)
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
                    'dummyName' => 'created',
                    'DummyName' => 'Created',
                    'DUMMY_NAME_AT' => 'CREATED_AT',
                ]));
            }

            if (!$this->hasColumn($UPDATED_AT)) {
                $skeleton->addMethod($this->util()->makeMethod('setUpdatedAt', $stub, [
                    'dummyName' => 'updated',
                    'DummyName' => 'Updated',
                    'DUMMY_NAME_AT' => 'UPDATED_AT',
                ]));
            }
        }
    }

    /**
     * Generate a timestamp in the skeleton with the replace values given by $field.
     *
     * @param Skeleton $skeleton
     * @param          $field
     *
     * @return mixed|null
     */
    protected function generateTimestamp(Skeleton $skeleton, $field)
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

    /**
     * Check if the column exists.
     *
     * @param $name
     *
     * @return bool
     */
    protected function hasColumn($name)
    {
        return $this->table()->hasColumn($name);
    }

    /**
     * Check if the column is a timestamp.
     *
     * @param $name
     *
     * @return bool
     */
    protected function columnIsTimestamp($name)
    {
        return $this->table()->getColumn($name)->getType()->getName() === Type::DATETIME;
    }

    /**
     * Retrieve the template.
     *
     * @return string
     */
    protected function voidTimestamp()
    {
        return $this->getFile($this->getStub($this->voidTimestamp_stub));
    }
}
