<?php

namespace Common\Service\Qa\Custom\Common;

use Common\Service\Qa\DateSelect as BaseDateSelect;

class DateSelectMustBeBefore extends BaseDateSelect
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        $inputSpecification = $this->callParentGetInputSpecification();

        $inputSpecification['validators'][] = [
            'name' => DateBeforeValidator::class,
            'options' => [
                'dateMustBeBefore' => $this->options['dateMustBeBefore'],
                'messages' => [
                    DateBeforeValidator::ERR_DATE_NOT_BEFORE => $this->options['dateNotBeforeKey']
                ]
            ]
        ];

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
