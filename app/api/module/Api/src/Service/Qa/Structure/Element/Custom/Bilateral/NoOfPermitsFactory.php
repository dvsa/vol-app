<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

class NoOfPermitsFactory
{
    /**
     * Create and return a NoOfPermits instance
     *
     * @return NoOfPermits
     */
    public function create()
    {
        return new NoOfPermits();
    }
}
