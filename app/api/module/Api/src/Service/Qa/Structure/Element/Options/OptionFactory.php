<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options;

class OptionFactory
{
    /**
     * Create instance
     *
     * @param string $value
     * @param string $label
     * @param string|null $hint
     *
     * @return Option
     */
    public function create($value, $label, $hint = null)
    {
        return new Option($value, $label, $hint);
    }
}
