<?php

namespace Triun\ModelBase\Modifiers;

use Triun\ModelBase\Lib\ModifierBase;
use Triun\ModelBase\Definitions\Property;
use Triun\ModelBase\Definitions\Skeleton;

/**
 * Class DatesModifier
 *
 * @package Triun\ModelBase\Modifiers
 */
class DatesModifier extends ModifierBase
{
    /**
     * Apply the modifications of the class.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     */
    public function apply(Skeleton $skeleton)
    {
        // Set date format if set.
        $this->setProperty($skeleton, 'dateFormat', $this->config('dateFormat'));

        // Check if dates is enabled in the config file
        if ($this->config('dates', true) !== true) {
            return;
        }

        // Retrieve dates property from the skeleton to edit it.
        $dates = $skeleton->property('dates');

        foreach ($this->table()->getColumns() as $column) {
            if ($column->isDate) {
                $name = $column->getName();

                $this->addToDates($name, $dates);

                // Add snake name too...
                if ($column->publicName !== $name) {
                    $this->addToDates($column->publicName, $dates);
                }
            }
        }
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
}
