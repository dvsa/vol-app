<?php

namespace Common\Form\Elements\Custom;

use Common\Filter\NotPopulatedStringToZero;
use Common\Service\Qa\Custom\Ecmt\NoOfPermitsBothValidator;

class EcmtNoOfPermitsBothElement extends EcmtNoOfPermitsElement
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        $inputSpecification = $this->callParentGetInputSpecification();

        $inputSpecification['filters'][] = [
            'name' => NotPopulatedStringToZero::class
        ];

        if (!$this->options['skipAvailabilityValidation']) {
            $inputSpecification['validators'][] = [
                'name' => NoOfPermitsBothValidator::class,
                'options' => [
                    'permitsRemaining' => $this->options['permitsRemaining'],
                    'emissionsCategory' => $this->options['emissionsCategory']
                ]
            ];
        }

        return $inputSpecification;
    }

    /**
     * Call getInputSpecification from parent class (to assist with unit testing)
     *
     * @return array
     */
    protected function callParentGetInputSpecification()
    {
        return parent::getInputSpecification();
    }
}
