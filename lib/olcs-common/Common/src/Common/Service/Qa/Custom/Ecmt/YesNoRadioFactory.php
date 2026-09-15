<?php

namespace Common\Service\Qa\Custom\Ecmt;

class YesNoRadioFactory
{
    /**
     * Create a YesNoRadio element instance with the supplied name
     *
     * @param string $name
     *
     * @return YesNoRadio
     */
    public function create($name)
    {
        return new YesNoRadio($name);
    }
}
