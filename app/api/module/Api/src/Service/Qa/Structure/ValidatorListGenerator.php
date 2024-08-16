<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;

class ValidatorListGenerator
{
    /**
     * Create service instance
     *
     *
     * @return ValidatorListGenerator
     */
    public function __construct(private readonly ValidatorListFactory $validatorListFactory, private readonly ValidatorGenerator $validatorGenerator)
    {
    }

    /**
     * Build and return a ValidatorList instance using the appropriate data sources
     *
     *
     * @return ValidatorList
     */
    public function generate(ApplicationStepEntity $applicationStepEntity)
    {
        $validatorList = $this->validatorListFactory->create();
        $applicationValidations = $applicationStepEntity->getQuestion()->getApplicationValidations();

        foreach ($applicationValidations as $applicationValidation) {
            $validatorList->addValidator(
                $this->validatorGenerator->generate($applicationValidation)
            );
        }

        return $validatorList;
    }
}
