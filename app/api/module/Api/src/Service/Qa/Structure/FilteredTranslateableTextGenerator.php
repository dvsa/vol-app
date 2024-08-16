<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

class FilteredTranslateableTextGenerator
{
    /**
     * Create service instance
     *
     *
     * @return FilteredTranslateableText
     */
    public function __construct(private readonly FilteredTranslateableTextFactory $filteredTranslateableTextFactory, private readonly TranslateableTextGenerator $translateableTextGenerator)
    {
    }

    /**
     * Build and return an FilteredTranslateableText instance using the appropriate data sources
     *
     *
     * @return FilteredTranslateableText
     */
    public function generate(array $options)
    {
        return $this->filteredTranslateableTextFactory->create(
            $options['filter'],
            $this->translateableTextGenerator->generate($options['translateableText'])
        );
    }
}
