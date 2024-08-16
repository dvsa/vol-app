<?php

namespace Dvsa\Olcs\Api\Service\Qa\Facade\SupplementedApplicationSteps;

use Dvsa\Olcs\Api\Domain\FormControlServiceManager;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;

class SupplementedApplicationStepsProvider
{
    /**
     * Create service instance
     *
     *
     * @return SupplementedApplicationStepsProvider
     */
    public function __construct(private readonly FormControlServiceManager $formControlServiceManager, private readonly SupplementedApplicationStepFactory $supplementedApplicationStepFactory)
    {
    }

    /**
     * Get a list of application steps and associated form control strategies for the specified qa entity
     *
     *
     * @return array
     */
    public function get(QaEntityInterface $qaEntity)
    {
        $applicationPath = $qaEntity->getActiveApplicationPath();

        $supplementedApplicationSteps = [];

        foreach ($applicationPath->getApplicationSteps() as $applicationStep) {
            $supplementedApplicationSteps[] = $this->supplementedApplicationStepFactory->create(
                $applicationStep,
                $this->formControlServiceManager->getByApplicationStep($applicationStep)
            );
        }

        return $supplementedApplicationSteps;
    }
}
