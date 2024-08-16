<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

class TranslateableTextGenerator
{
    /**
     * Create service instance
     *
     *
     * @return TranslateableTextGenerator
     */
    public function __construct(private readonly TranslateableTextFactory $translateableTextFactory, private readonly TranslateableTextParameterGenerator $translateableTextParameterGenerator)
    {
    }

    /**
     * Build and return a TranslateableText instance using the appropriate data sources
     *
     *
     * @return TranslateableText
     */
    public function generate(array $options)
    {
        $translateableText = $this->translateableTextFactory->create($options['key']);

        if (isset($options['parameters'])) {
            foreach ($options['parameters'] as $parameter) {
                $translateableText->addParameter(
                    $this->translateableTextParameterGenerator->generate($parameter)
                );
            }
        }

        return $translateableText;
    }
}
