<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\ValidatorList;

class ElementGeneratorContextFactory
{
    /**
     * Create and return an ElementGeneratorContext instance
     *
     * @param string $elementContainer
     *
     * @return ElementGeneratorContext
     */
    public function create(ValidatorList $validatorList, QaContext $qaContext, $elementContainer)
    {
        return new ElementGeneratorContext($validatorList, $qaContext, $elementContainer);
    }
}
