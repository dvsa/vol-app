<?php

namespace Dvsa\Olcs\Transfer\Util\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayInput
{
    protected bool $arrayInput = false;

    public function __construct(bool $arrayInput = true)
    {
        $this->arrayInput = $arrayInput;
    }

    public function getArrayInput(): bool
    {
        return $this->arrayInput;
    }
}
