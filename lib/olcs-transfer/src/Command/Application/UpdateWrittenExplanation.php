<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\AbstractIdWithVersionCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/application/single/written-explanation")
 * @Transfer\Method("PUT")
 */
final class UpdateWrittenExplanation extends AbstractIdWithVersionCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $psvSmallVhlNotes;

    /**
     * @Transfer\Filter("Laminas\Filter\ToInt")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 0, "max": 99999})
     */
    protected $psvTotalVehicleSmall;

    /**
     * @Transfer\Filter("Laminas\Filter\ToInt")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 0, "max": 99999})
     */
    protected $psvTotalVehicleLarge;

    public function getPsvSmallVhlNotes(): ?string
    {
        return $this->psvSmallVhlNotes;
    }

    public function getPsvTotalVehicleSmall(): ?int
    {
        return $this->psvTotalVehicleSmall;
    }

    public function getPsvTotalVehicleLarge(): ?int
    {
        return $this->psvTotalVehicleLarge;
    }
}
