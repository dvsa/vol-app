<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\ValidatorListGenerator;

class ElementGeneratorContextGenerator
{
    /**
     * Create service instance
     */
    public function __construct(private readonly ValidatorListGenerator $validatorListGenerator, private readonly ElementGeneratorContextFactory $elementGeneratorContextFactory)
    {
    }

    /**
     * Create and return an ElementGeneratorContext instance
     *
     *
     * @return ElementGeneratorContext
     */
    public function generate(QaContext $qaContext, $elementContainer)
    {
        $validatorList = $this->validatorListGenerator->generate(
            $qaContext->getApplicationStepEntity()
        );

        return $this->elementGeneratorContextFactory->create(
            $validatorList,
            $qaContext,
            $elementContainer
        );
    }
}
