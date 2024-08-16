<?php

namespace Dvsa\Olcs\Api\Service\Qa\Facade\SupplementedApplicationSteps;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\Strategy\FormControlStrategyInterface;

class SupplementedApplicationStepFactory
{
    /**
     * Create and return a SupplementedApplicationStep instance
     *
     *
     * @return SupplementedApplicationStep
     */
    public function create(ApplicationStep $applicationStep, FormControlStrategyInterface $formControlStrategy)
    {
        return new SupplementedApplicationStep($applicationStep, $formControlStrategy);
    }
}
