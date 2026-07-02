<?php

namespace Common\Service\Qa\Custom\Bilateral;

class StandardAndCabotageYesNoRadioFactory
{
    /**
     * Create a StandardAndCabotageYesNoRadio element instance with the supplied name
     *
     * @param string $name
     *
     * @return StandardAndCabotageYesNoRadio
     */
    public function create($name)
    {
        return new StandardAndCabotageYesNoRadio($name);
    }
}
