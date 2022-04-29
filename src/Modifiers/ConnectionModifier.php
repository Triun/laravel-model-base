<?php

namespace Triun\ModelBase\Modifiers;

use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Lib\ModifierBase;

class ConnectionModifier extends ModifierBase
{
    public function apply(Skeleton $skeleton): void
    {
        $skeleton->property('connection')->setValue($this->connection()->getName());
    }
}
