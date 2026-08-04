<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Common\Service\Qa\Custom\Common\WarningAdder;
use Laminas\Form\Fieldset;

class NiWarningConditionalAdder
{
    /**
     * Create service instance
     *
     *
     * @return NiWarningConditionalAdder
     */
    public function __construct(private WarningAdder $warningAdder)
    {
    }

    /**
     * Add the NI warning to the fieldset if showNiWarning is true
     *
     * @param bool $showNiWarning
     */
    public function addIfRequired(Fieldset $fieldset, $showNiWarning): void
    {
        if ($showNiWarning) {
            $this->warningAdder->add(
                $fieldset,
                'permits.page.number-of-trips.northern-ireland.warning',
                WarningAdder::DEFAULT_PRIORITY,
                'niWarning'
            );
        }
    }
}
