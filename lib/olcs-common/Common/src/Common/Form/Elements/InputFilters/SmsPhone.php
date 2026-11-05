<?php

namespace Common\Form\Elements\InputFilters;

use Dvsa\Olcs\Transfer\Validators\SmsPhoneNumber;
use Laminas\Form\Element as LaminasElement;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\NotEmpty;

/**
 * Optional mobile or landline number used for SMS notifications
 */
class SmsPhone extends LaminasElement implements InputProviderInterface
{
    protected $required = false;

    #[\Override]
    public function init(): void
    {
        // Labelled optional; form services that make it required swap to contact-number-sms.
        $this->setLabel('contact-number-sms-optional');
        $this->setOption('hint', 'contact-number-sms-hint');
        parent::init();
    }

    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => $this->required,
            'filters' => [
                ['name' => \Laminas\Filter\StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => 'contact-number-sms-required',
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => SmsPhoneNumber::class,
                ],
            ],
        ];
    }
}
