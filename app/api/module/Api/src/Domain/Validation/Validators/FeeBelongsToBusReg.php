<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Fee Belongs To Bus Reg
 *
 * Confirms a fee is attached to a given bus registration.
 */
class FeeBelongsToBusReg extends AbstractBelongsToBusReg
{
    protected $repo = 'Fee';
}
