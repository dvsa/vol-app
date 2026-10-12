<?php

namespace Dvsa\Olcs\Transfer\Util\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ContinueIfEmpty
{
    protected bool $continueIfEmpty = false;

    public function __construct(bool $continueIfEmpty = true)
    {
        $this->continueIfEmpty = $continueIfEmpty;
    }

    public function getContinueIfEmpty(): bool
    {
        return $this->continueIfEmpty;
    }
}
