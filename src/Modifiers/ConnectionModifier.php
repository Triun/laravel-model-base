<?php

namespace Triun\ModelBase\Modifiers;

use Triun\ModelBase\Lib\ModifierBase;
use Triun\ModelBase\Definitions\Skeleton;

/**
 * Class ConnectionModifier
 *
 * @package Triun\ModelBase\Modifiers
 */
class ConnectionModifier extends ModifierBase
{
    /**
     * Apply the modifications of the class.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     */
    public function apply(Skeleton $skeleton)
    {
        $skeleton->property('connection')->setValue($this->connection()->getName());
    }
}
