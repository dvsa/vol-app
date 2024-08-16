<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options;

class OptionListFactory
{
    /**
     * Create instance
     *
     *
     * @return OptionList
     */
    public function create(OptionFactory $optionFactory)
    {
        return new OptionList($optionFactory);
    }
}
