<?php

namespace Triun\ModelBase\Exception;

use Exception;

class SkeletonUseNameException extends \InvalidArgumentException
{
    private string $name;
    private string $attemptedAlias;
    private string $actualAlias;

    public function __construct(
        string $name,
        string $attemptedAlias,
        string $actualAlias,
        int $code = 0,
        ?Exception $previous = null
    ) {
        $this->name           = $name;
        $this->attemptedAlias = $attemptedAlias;
        $this->actualAlias    = $actualAlias;

        parent::__construct(
            "Cannot use $name as $attemptedAlias because the name is already aliased as $actualAlias",
            $code,
            $previous
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAttemptedAlias(): string
    {
        return $this->attemptedAlias;
    }

    public function getActualAlias(): string
    {
        return $this->actualAlias;
    }
}
