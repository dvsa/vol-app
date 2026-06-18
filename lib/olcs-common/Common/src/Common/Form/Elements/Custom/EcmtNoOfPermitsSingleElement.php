<?php

namespace Common\Form\Elements\Custom;

use Common\Service\Qa\Custom\Ecmt\NoOfPermitsSingleValidator;
use Laminas\Validator\GreaterThan;

class EcmtNoOfPermitsSingleElement extends EcmtNoOfPermitsElement
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        $inputSpecification = $this->callParentGetInputSpecification();

        $inputSpecification['validators'][] = [
            'name' => GreaterThan::class,
            'options' => [
                'min' => 0,
                'messages' => [
                    GreaterThan::NOT_GREATER => self::GENERIC_ERROR_KEY
                ]
            ]
        ];

        if (!$this->options['skipAvailabilityValidation']) {
            $inputSpecification['validators'][] = [
                'name' => NoOfPermitsSingleValidator::class,
                'options' => [
                    'maxPermitted' => $this->options['maxPermitted'],
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
