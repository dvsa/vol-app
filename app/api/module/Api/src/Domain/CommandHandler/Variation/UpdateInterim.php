<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Variation;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractUpdateInterim;

final class UpdateInterim extends AbstractUpdateInterim
{
    protected $allowZeroAuthVehicles = true;
}
