<?php

namespace Triun\ModelBase\Lib;

use Triun\ModelBase\ModelBaseConfig;

abstract class UtilBase
{
    protected ModelBaseConfig $config;

    public function __construct(ModelBaseConfig $config)
    {
        $this->config = $config;

        $this->init();
    }

    protected function init(): void
    {
        // Requirements etc.
    }

    /**
     * @return ModelBaseConfig|mixed
     */
    public function config(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->config : $this->config->get($key, $default);
    }

    /**
     * fnmatch separated by |
     * http://php.net/fnmatch
     * '*gr[ae]y' is gray and grey
     * 'gray|grey' is also gray and grey
     * '*At|*_at finish in 'At' or '_at'
     */
    public function match(array|string $rules, string $value, bool $case_sensitive = false): bool
    {
        return $this->config->match($rules, $value, $case_sensitive);
    }
}
