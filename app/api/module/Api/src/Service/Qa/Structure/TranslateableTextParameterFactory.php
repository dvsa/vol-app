<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

class TranslateableTextParameterFactory
{
    /**
     * Create and return a TranslateableTextParameter instance
     *
     * @param string $value
     * @param string|null $formatter
     *
     * @return TranslateableTextParameter
     */
    public function create($value, $formatter)
    {
        return new TranslateableTextParameter($value, $formatter);
    }
}
