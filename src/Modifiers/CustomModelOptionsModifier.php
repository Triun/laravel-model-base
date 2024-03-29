<?php

declare(strict_types=1);

namespace Triun\ModelBase\Modifiers;

use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Lib\ModifierBase;

class CustomModelOptionsModifier extends ModifierBase
{
    private array $default = [
        'interfaces' => [],
        'traits'     => [],
        'uses'       => [],
        'phpDocTags' => [],
    ];

    public function apply(Skeleton $skeleton): void
    {
        $config = $this->getConfig();

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

        foreach ($config['phpDocTags'] as $key => $value) {
            if ($skeleton->hasPhpDocTag($key) && array_key_exists('type', $value)) {
                $skeleton->phpDocTag($key)->type = $value['type'];
            }
        }
    }

    private function getConfig(): array
    {
        $tableName = $this->table()->getName();

        $rawConfig = $this->config('custom_model_options', []);

        $config = $this->default;

        if (array_key_exists('default', $rawConfig)) {
            $config = array_merge($config, $rawConfig['default']);
        }

        if (array_key_exists('tables', $rawConfig) && array_key_exists($tableName, $rawConfig['tables'])) {
            return array_merge($config, $rawConfig['tables'][$tableName]);
        }

        return $config;
    }
}
