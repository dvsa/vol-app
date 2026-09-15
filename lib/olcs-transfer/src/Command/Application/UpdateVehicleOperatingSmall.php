<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Version;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractIdWithVersionCommand;

/**
 * @Transfer\RouteName("backend/application/single/vehicle-operating-small")
 * @Transfer\Method("PUT")
 */
final class UpdateVehicleOperatingSmall extends AbstractIdWithVersionCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $psvOperateSmallVhl;

    public function getPsvOperateSmallVhl(): ?string
    {
        return $this->psvOperateSmallVhl;
    }
}
