<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Common\Form\Elements\Types\Radio as CommonRadio;

class Radio extends CommonRadio
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        $spec = $this->callParentGetInputSpecification();

        $spec['required'] = false;

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
