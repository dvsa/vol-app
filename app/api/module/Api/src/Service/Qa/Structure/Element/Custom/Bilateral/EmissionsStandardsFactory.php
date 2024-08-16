<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

class EmissionsStandardsFactory
{
    /**
     * Create and return a EmissionsStandards instance
     *
     * @param string|null $yesNo
     *
     * @return EmissionsStandards
     */
    public function create($yesNo)
    {
        return new EmissionsStandards($yesNo);
    }
}
