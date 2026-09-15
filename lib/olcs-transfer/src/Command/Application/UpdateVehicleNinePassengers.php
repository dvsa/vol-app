<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractIdWithVersionCommand;

/**
 * @Transfer\RouteName("backend/application/single/vehicle-nine-passengers")
 * @Transfer\Method("PUT")
 */
final class UpdateVehicleNinePassengers extends AbstractIdWithVersionCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $psvNoSmallVhlConfirmation;

    public function getPsvNoSmallVhlConfirmation(): ?string
    {
        return $this->psvNoSmallVhlConfirmation;
    }
}
