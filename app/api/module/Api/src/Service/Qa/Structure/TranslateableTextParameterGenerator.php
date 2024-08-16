<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

class TranslateableTextParameterGenerator
{
    /**
     * Create service instance
     *
     * @param TranslateableTextParameterFactory $translateableTextFactory
     *
     * @return TranslateableTextParameterGenerator
     */
    public function __construct(private readonly TranslateableTextParameterFactory $translateableTextParameterFactory)
    {
    }

    /**
     * Build and return a TranslateableTextParameter instance using the appropriate data sources
     *
     *
     * @return TranslateableTextParameter
     */
    public function generate(array $options)
    {
        $formatter = null;
        if (isset($options['formatter'])) {
            $formatter = $options['formatter'];
        }

        return $this->translateableTextParameterFactory->create($options['value'], $formatter);
    }
}
