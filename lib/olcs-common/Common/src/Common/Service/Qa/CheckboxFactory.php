<?php

namespace Common\Service\Qa;

use Common\Form\Elements\InputFilters\SingleCheckbox;

class CheckboxFactory
{
    /**
     * Create a checkbox element instance with the supplied name
     *
     * @param string $name
     *
     * @return SingleCheckbox
     */
    public function create($name)
    {
        return new SingleCheckbox($name);
    }
}
