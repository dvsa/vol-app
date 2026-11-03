<?php

/**
 * DoNotExchange
 *
 * Using this annotation on a Query property will prevent AbstractQuery exchangeArray method to set the property
 *
 * You'll need to use this together with the Transfer\Optional annotation
 */

namespace Dvsa\Olcs\Transfer\Util\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DoNotExchange
{
    protected bool $doNotExchange = true;

    public function __construct(bool $doNotExchange = true)
    {
        $this->doNotExchange = $doNotExchange;
    }

    public function getDoNotExchange(): bool
    {
        return $this->doNotExchange;
    }
}
