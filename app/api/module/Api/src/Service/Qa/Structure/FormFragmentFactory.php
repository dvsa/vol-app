<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

class FormFragmentFactory
{
    /**
     * Create and return a FormFragment instance
     *
     * @return FormFragment
     */
    public function create()
    {
        return new FormFragment();
    }
}
