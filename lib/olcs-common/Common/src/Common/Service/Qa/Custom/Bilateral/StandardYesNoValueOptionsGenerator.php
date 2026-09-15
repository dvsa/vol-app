<?php

namespace Common\Service\Qa\Custom\Bilateral;

class StandardYesNoValueOptionsGenerator
{
    /**
     * Create service instance
     *
     *
     * @return StandardYesNoValueOptionsGenerator
     */
    public function __construct(private YesNoValueOptionsGenerator $yesNoValueOptionsGenerator)
    {
    }

    /**
     * Generate an array of standard value options for a yes/no radio element
     *
     * @return array
     */
    public function generate()
    {
        return $this->yesNoValueOptionsGenerator->generate('Yes', 'No');
    }
}
