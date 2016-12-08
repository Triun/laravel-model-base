<?php


namespace Triun\ModelBase\Lib;

use Triun\ModelBase\ModelBaseConfig;

abstract class UtilBase
{
    /**
     * Configuration
     *
     * @var \Triun\ModelBase\ModelBaseConfig
     */
    protected $_config;

    /**
     * ModelBaseUtil constructor.
     *
     * @param \Triun\ModelBase\ModelBaseConfig  $config
     */
    public function __construct(ModelBaseConfig $config)
    {
        $this->_config = $config;

        $this->init();
    }

    /**
     * Initialize Util
     */
    protected function init()
    {
        // Requirements etc.
    }

    /**
     * Get the configuration.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return ModelBaseConfig
     */
    public function config($key = null, $default = null)
    {
        return $key === null ? $this->_config : $this->_config->get($key, $default);
    }

    /**
     * fnmatch separated by |
     * http://php.net/fnmatch
     * '*gr[ae]y' is gray and grey
     * 'gray|grey' is also gray and grey
     * '*At|*_at finish in 'At' or '_at'
     *
     * @param string|string[]   $rules
     * @param string            $value
     * @param bool              $case_sensitive
     *
     * @return bool
     */
    public function match($rules, $value, $case_sensitive = false)
    {
        return $this->_config->match($rules, $value, $case_sensitive);
    }
}