<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;

class ApplicationStepFactory
{
    /**
     * Create and return an ApplicationStep instance
     *
     * @param string $type
     * @param string $fieldsetName
     * @param string $shortName
     * @param string $slug
     * @param bool $enabled
     *
     * @return ApplicationStep
     */
    public function create(
        $type,
        $fieldsetName,
        $shortName,
        $slug,
        $enabled,
        ElementInterface $element,
        ValidatorList $validatorList
    ) {
        return new ApplicationStep($type, $fieldsetName, $shortName, $slug, $enabled, $element, $validatorList);
    }
}
