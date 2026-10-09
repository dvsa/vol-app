<?php

/**
 * Escape
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Util\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Escape
{
    protected bool $escape = true;

    public function __construct(bool $escape = true)
    {
        $this->escape = $escape;
    }

    public function getEscape(): bool
    {
        return $this->escape;
    }
}
