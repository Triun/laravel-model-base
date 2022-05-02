<?php

namespace Triun\ModelBase\Modifiers;

use Triun\ModelBase\Definitions\Property;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Lib\ModifierBase;

class DatesModifier extends ModifierBase
{
    public function apply(Skeleton $skeleton): void
    {
        // Set date format if set.
        $this->setProperty($skeleton, 'dateFormat', $this->config('dateFormat'));

        // Check if dates is enabled in the config file
        // Notice that this property has been deprecated in Laravel 9.x
        if (true !== $this->config('datesProperty', false)) {
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

    protected function addToDates(string $name, Property $dates): void
    {
        // Add to dates array
        if (!in_array($name, $dates->value)) {
            $dates->value[] = $name;
        }
    }
}
