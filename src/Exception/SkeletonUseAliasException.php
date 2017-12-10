<?php

namespace Triun\ModelBase\Exception;

/**
 * Class SkeletonUseNameException
 *
 * @package Triun\ModelBase\Exception
 */
class SkeletonUseAliasException extends \InvalidArgumentException
{
    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    private $attemptedName;

    /**
     * @var string
     */
    private $actualName;

    /**
     * SkeletonUseNameException constructor.
     *
     * @param string          $alias         Basename or alias.
     * @param string          $attemptedName Full name, included namespace, that was attempted to be use by the alias.
     * @param string          $actualName    Full name, included namespace, that was already use by the alias.
     * @param int             $code          [optional] The Exception code.
     * @param \Exception|null $previous      [optional] The previous exception used for the exception chaining.
     *                                       Since 5.3.0
     *
     * @since 5.1.0
     */
    public function __construct($alias, $attemptedName, $actualName, $code = 0, \Exception $previous = null)
    {
        $this->alias = $alias;
        $this->attemptedName = $attemptedName;
        $this->actualName = $actualName;

        parent::__construct("Cannot use $attemptedName as $alias because the alias is already set as $actualName");
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getAttemptedName()
    {
        return $this->attemptedName;
    }

    /**
     * @return string
     */
    public function getActualName()
    {
        return $this->actualName;
    }
}
