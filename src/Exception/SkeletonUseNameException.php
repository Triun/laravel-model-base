<?php

namespace Triun\ModelBase\Exception;

/**
 * Class SkeletonUseNameException
 * @package Triun\ModelBase\Exception
 */
class SkeletonUseNameException extends \InvalidArgumentException
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $attemptedAlias;

    /**
     * @var string
     */
    private $actualAlias;

    /**
     * SkeletonUseNameException constructor.
     *
     * @param string          $name Full name of the class, included namespace.
     * @param string          $attemptedAlias Basename or alias that was attempted to be set.
     * @param string          $actualAlias Basename or alias that was already set.
     * @param int             $code [optional] The Exception code.
     * @param \Exception|null $previous [optional] The previous exception used for the exception chaining. Since 5.3.0
     * @since 5.1.0
     */
    public function __construct($name, $attemptedAlias, $actualAlias, $code = 0, \Exception $previous = null)
    {
        $this->name = $name;
        $this->attemptedAlias = $attemptedAlias;
        $this->actualAlias = $actualAlias;

        parent::__construct(
            "Cannot use $name as $attemptedAlias because the name is already aliased as $actualAlias",
            $code,
            $previous
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAttemptedAlias()
    {
        return $this->attemptedAlias;
    }

    /**
     * @return string
     */
    public function getActualAlias()
    {
        return $this->actualAlias;
    }
}
