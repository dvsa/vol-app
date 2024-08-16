<?php

namespace Dvsa\Olcs\Api\Service\Qa\Facade\SupplementedApplicationSteps;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\Strategy\FormControlStrategyInterface;

class SupplementedApplicationStep
{
    /**
     * Create instance
     *
     *
     * @return SupplementedApplicationStep
     */
    public function __construct(private readonly ApplicationStep $applicationStep, private readonly FormControlStrategyInterface $formControlStrategy)
    {
    }

    /**
     * Get the embedded ApplicationStep instance
     *
     * @return ApplicationStep
     */
    public function getApplicationStep()
    {
        return $this->applicationStep;
    }

    /**
     * Get the embedded FormControlStrategyInterface instance
     *
     * @return FormControlStrategyInterface
     */
    public function getFormControlStrategy()
    {
        return $this->formControlStrategy;
    }
}
