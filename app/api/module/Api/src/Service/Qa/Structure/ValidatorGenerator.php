<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationValidation;

class ValidatorGenerator
{
    /**
     * Create service instance
     *
     *
     * @return ValidatorGenerator
     */
    public function __construct(private readonly ValidatorFactory $validatorFactory)
    {
    }

    /**
     * Build and return a Validator instance using the appropriate data sources
     *
     *
     * @return Validator
     */
    public function generate(ApplicationValidation $applicationValidation)
    {
        return $this->validatorFactory->create(
            $applicationValidation->getRule(),
            json_decode(
                $applicationValidation->getParameters(),
                true
            )
        );
    }
}
