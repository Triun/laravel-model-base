<?php

declare(strict_types=1);

namespace Triun\ModelBase\Modifiers;

use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Lib\ModifierBase;

/**
 * Class CustomModelOptionsModifier
 *
 * @package Triun\ModelBase\Modifiers
 */
class CustomModelOptionsModifier extends ModifierBase
{
    private $default = [
        'interfaces' => [],
        'traits'     => [],
        'uses'       => [],
    ];

    /**
     * Apply the modifications of the class.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     */
    public function apply(Skeleton $skeleton)
    {
        $config = array_merge($this->default, $this->config('model.custom', []));

        foreach ($config['interfaces'] as $key => $value) {
            if (is_string($key)) {
                $skeleton->addInterface($key, $value);
            } else {
                $skeleton->addInterface($value);
            }
        }

        foreach ($config['traits'] as $key => $value) {
            if (is_string($key)) {
                $skeleton->addTrait($key, $value);
            } else {
                $skeleton->addTrait($value);
            }
        }

        foreach ($config['uses'] as $key => $value) {
            if (is_string($key)) {
                $skeleton->addUse($key, $value);
            } else {
                $skeleton->addUse($value);
            }
        }
    }
}
