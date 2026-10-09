<?php

namespace Dvsa\Olcs\Transfer\Util\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Optional
{
    protected bool $optional = false;


    public function __construct(bool $optional = true)
    {
        $this->optional = $optional;
    }

    public function getOptional(): bool
    {
        return $this->optional;
    }
}
