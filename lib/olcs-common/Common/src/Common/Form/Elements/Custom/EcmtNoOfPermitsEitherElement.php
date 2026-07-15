<?php

namespace Common\Form\Elements\Custom;

use Common\Service\Qa\Custom\Ecmt\NoOfPermitsEitherValidator;
use Laminas\Validator\GreaterThan;

class EcmtNoOfPermitsEitherElement extends EcmtNoOfPermitsElement
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
                'name' => NoOfPermitsEitherValidator::class,
                'options' => [
                    'maxPermitted' => $this->options['maxPermitted'],
                    'emissionsCategoryPermitsRemaining' => $this->options['emissionsCategoryPermitsRemaining']
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
