<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

class JsonDecodingFilteredTranslateableTextGenerator
{
    /**
     * Create service instance
     *
     *
     * @return JsonDecodingFilteredTranslateableTextGenerator
     */
    public function __construct(private readonly FilteredTranslateableTextGenerator $filteredTranslateableTextGenerator)
    {
    }

    /**
     * Build and return a FilteredTranslateableTextGenerator from the supplied value if the value is a string,
     * otherwise return null
     *
     * @param string|null $value
     *
     * @return FilteredTranslateableText|null
     */
    public function generate($value)
    {
        if (is_string($value)) {
            return $this->filteredTranslateableTextGenerator->generate(
                json_decode($value, true)
            );
        }

        return null;
    }
}
