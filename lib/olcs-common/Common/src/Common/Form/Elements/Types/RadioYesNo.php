<?php

namespace Common\Form\Elements\Types;

/**
 * Radio YesNo form element
 */
class RadioYesNo extends Radio
{
    /**
     * Initial value options
     */
    #[\Override]
    public function init(): void
    {
        $this->setValueOptions(['Y' => 'Yes', 'N' => 'No']);

        parent::init();
    }
}
