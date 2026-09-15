<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Common\Form\Elements\InputFilters\QaRadio;

class StandardAndCabotageYesNoRadio extends QaRadio
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        $spec = $this->callParentGetInputSpecification();

        $spec['validators'][] = new StandardAndCabotageYesNoRadioValidator(
            $this->options['yesContentElement']
        );

        return $spec;
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
