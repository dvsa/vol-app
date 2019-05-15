<?php

namespace Olcs\Service\Qa;

use RuntimeException;

class FormProvider
{
    const CONTROL_TYPE_INDEX = 'type';
    const DATA_INDEX = 'data';
    const VALIDATORS_INDEX = 'validators';

    /** @var array */
    private $formTypeProviderMappings;

    /**
     * Create service instance
     *
     * @param array $formTypeProviderMappings
     *
     * @return FormProvider
     */
    public function __construct(array $formTypeProviderMappings)
    {
        $this->formTypeProviderMappings = $formTypeProviderMappings;
    }

    /**
     * Get a Form instance corresponding to the supplied form data
     *
     * @param array $formData
     *
     * @return mixed
     */
    public function get(array $formData)
    {
        if (!isset($formData[self::CONTROL_TYPE_INDEX])) {
            throw new RuntimeException('No type attribute found in form data');
        }

        $type = $formData[self::CONTROL_TYPE_INDEX];
        if (!isset($this->formTypeProviderMappings[$type])) {
            throw new RuntimeException('No mapping found for type ' . $type);
        } 

        $formTypeProvider = $this->formTypeProviderMappings[$type];
        return $formTypeProvider->get($formData[self::DATA_INDEX], $formData[self::VALIDATORS_INDEX]);
    }
}
