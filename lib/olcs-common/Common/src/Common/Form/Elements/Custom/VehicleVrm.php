<?php

namespace Common\Form\Elements\Custom;

use Common\Filter\Vrm;
use Dvsa\Olcs\Transfer\Validators\Vrm as VrmValidator;
use Laminas\Form\Element as LaminasElement;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\NotEmpty;

/**
 * Vrm field for UK vehicles
 */
class VehicleVrm extends LaminasElement implements InputProviderInterface
{
    /**
     * Provide default input rules for this element.
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => true,
            'filters' => [
                new Vrm(),
            ],
            'validators' => $this->getValidators(),
        ];
    }

    /**
     * @return (string|string[][]|true)[][]
     *
     * @psalm-return list{0: array{name: NotEmpty::class, break_chain_on_failure: true, options: array{messages: array{isEmpty: 'licence.vehicle.add.search.vrm-missing'}}}, 1?: array{name: VrmValidator::class}}
     */
    protected function getValidators(): array
    {
        $validators = [
            [
                'name' => NotEmpty::class,
                'break_chain_on_failure' => true,
                'options' => [
                    'messages' => [
                        NotEmpty::IS_EMPTY => 'licence.vehicle.add.search.vrm-missing'
                    ]
                ],
            ]
        ];

        if ($this->getOption('validateVrm') ?? true) {
            $validators[] = [
                'name' => VrmValidator::class
            ];
        }

        return $validators;
    }
}
