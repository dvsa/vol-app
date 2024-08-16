<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;

interface ElementGeneratorInterface
{
    /**
     * Build and return an element instance using the appropriate data sources
     *
     *
     * @return ElementInterface
     */
    public function generate(ElementGeneratorContext $context);

    /**
     * Whether this element generator supports the specified entity
     *
     *
     * @return bool
     */
    public function supports(QaEntityInterface $qaEntity);
}
