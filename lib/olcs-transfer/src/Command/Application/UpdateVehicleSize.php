<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractIdWithVersionCommand;

/**
 * @Transfer\RouteName("backend/application/single/vehicle-size")
 * @Transfer\Method("PUT")
 */
final class UpdateVehicleSize extends AbstractIdWithVersionCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"psvvs_small", "psvvs_medium_large", "psvvs_both"}})
     */
    protected $psvVehicleSize;

    public function getPsvVehicleSize(): ?string
    {
        return $this->psvVehicleSize;
    }
}
