<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

class NoOfPermitsMoroccoFactory
{
    /**
     * Create and return a NoOfPermitsMorocco instance
     *
     * @param string $label
     * @param string|null $value
     *
     * @return NoOfPermitsMorocco
     */
    public function create($label, $value)
    {
        return new NoOfPermitsMorocco($label, $value);
    }
}
