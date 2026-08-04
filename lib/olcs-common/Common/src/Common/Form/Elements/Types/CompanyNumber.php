<?php

/**
 * CompanyNumber
 *
 * @author Someone <someone@valtech.co.uk>
 */

namespace Common\Form\Elements\Types;

use Laminas\Form\Fieldset;

/**
 * CompanyNumber
 *
 * @author Someone <someone@valtech.co.uk>
 */
class CompanyNumber extends Fieldset
{
    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);

        $this->setAttribute('class', 'lookup');

        $this->add(
            [
                'type' => \Common\Form\Elements\Types\PlainText::class,
                'name' => 'description',
                'attributes' => [
                    'data-container-class' => 'hint',
                ],
                'options' => [
                    'value' => 'selfserve-business-registered-company-description'
                ]
            ]
        );

        $this->add(
            [
                'type' => \Common\Form\Elements\InputFilters\CompanyNumber::class,
                'name' => 'company_number',
                'attributes' => [
                    'data-container-class' => 'inline',
                    'pattern' => '\d*'
                ],
            ]
        );

        $this->add(
            [
                'type' => 'button',
                'name' => 'submit_lookup_company',
                'options' => [
                    'label' => 'Find company',
                ],
                'attributes' => [
                    'class' => 'govuk-button',
                    'data-container-class' => 'inline',
                    'type' => 'submit',
                ],
            ]
        );
    }


    #[\Override]
    public function setMessages($messages): void
    {
        $this->messages = $messages;
    }

    #[\Override]
    public function getMessages(?string $elementName = null): array
    {
        return is_array($current = current($this->messages)) ? $current : [];
    }
}
