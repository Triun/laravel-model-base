<?php

namespace Triun\ModelBase\Exception;

use Exception;

class SkeletonUseAliasException extends \InvalidArgumentException
{
    private string $alias;
    private string $attemptedName;
    private string $actualName;

    public function __construct(
        string $alias,
        string $attemptedName,
        string $actualName,
        int $code = 0,
        Exception $previous = null
    ) {
        $this->alias         = $alias;
        $this->attemptedName = $attemptedName;
        $this->actualName    = $actualName;

        parent::__construct(
            "Cannot use $attemptedName as $alias because the alias is already set as $actualName",
            $code,
            $previous,
        );
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getAttemptedName(): string
    {
        return $this->attemptedName;
    }

    public function getActualName(): string
    {
        return $this->actualName;
    }
}
