<?php

/**
 * VehicleNumber validation
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */

namespace Common\Form\Elements\InputFilters;

use Laminas\Form\Element as LaminasElement;
use Laminas\Validator as LaminasValidator;
use Laminas\InputFilter\InputProviderInterface;
use Common\Form\Elements\Validators\VehiclesNumber as VehiclesNumberValidator;

/**
 * VehicleNumber validation
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
class VehiclesNumber extends LaminasElement implements InputProviderInterface
{
    /**
     * Provide default input rules for this element.
     *
     * @return ((LaminasValidator\Digits|VehiclesNumberValidator)[]|null|string|true)[]
     *
     * @psalm-return array{name: null|string, required: true, validators: list{LaminasValidator\Digits, VehiclesNumberValidator}}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => true,
            'validators' => [
                new LaminasValidator\Digits(),
                new VehiclesNumberValidator($this->getName())
            ]
        ];
    }
}
