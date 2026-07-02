<?php

namespace Common\Service\Qa;

use Common\Form\Elements\InputFilters\QaRadio;

class RadioFactory
{
    /**
     * Create a checkbox element instance with the supplied name
     *
     * @param string $name
     *
     * @return QaRadio
     */
    public function create($name)
    {
        return new QaRadio($name);
    }
}
