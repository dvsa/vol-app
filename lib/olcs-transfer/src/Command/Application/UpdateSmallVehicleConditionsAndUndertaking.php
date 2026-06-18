<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\AbstractIdWithVersionCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/application/single/small-vehicle-conditions")
 * @Transfer\Method("PUT")
 */
final class UpdateSmallVehicleConditionsAndUndertaking extends AbstractIdWithVersionCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $psvSmallVhlConfirmation;

    public function getPsvSmallVhlConfirmation(): ?string
    {
        return $this->psvSmallVhlConfirmation;
    }
}
