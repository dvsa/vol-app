<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;

class ApplicationStep
{
    /**
     * Create instance
     *
     * @param string $type
     * @param string $fieldsetName
     * @param string $shortName
     * @param string $slug
     * @param string $enabled
     *
     * @return ApplicationStep
     */
    public function __construct(private $type, private $fieldsetName, private $shortName, private $slug, private $enabled, private readonly ElementInterface $element, private readonly ValidatorList $validatorList)
    {
    }

    /**
     * Get the representation of this class to be returned by the API endpoint
     *
     * @return array
     */
    public function getRepresentation()
    {
        return [
            'type' => $this->type,
            'fieldsetName' => $this->fieldsetName,
            'shortName' => $this->shortName,
            'slug' => $this->slug,
            'enabled' => $this->enabled,
            'element' => $this->element->getRepresentation(),
            'validators' => $this->validatorList->getRepresentation(),
        ];
    }

    /**
     * Get the embedded ValidatorList instance
     *
     * @return ValidatorList
     */
    public function getValidatorList()
    {
        return $this->validatorList;
    }
}
