<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

class NoOfPermitsTextFactory
{
    /**
     * Create and return a NoOfPermitsText instance
     *
     * @param string $name
     * @param string $label
     * @param string $hint
     * @param string|null $value
     *
     * @return NoOfPermitsText
     */
    public function create($name, $label, $hint, $value)
    {
        return new NoOfPermitsText($name, $label, $hint, $value);
    }
}
