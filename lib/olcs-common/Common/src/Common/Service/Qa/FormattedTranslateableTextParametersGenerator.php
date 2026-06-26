<?php

namespace Common\Service\Qa;

use Common\Service\Helper\TranslationHelperService;

class FormattedTranslateableTextParametersGenerator
{
    /**
     * Create service instance
     *
     *
     * @return FormattedTranslateableTextParametersGenerator
     */
    public function __construct(private TranslateableTextParameterHandler $translateableTextParameterHandler)
    {
    }

    /**
     * @return string[]
     *
     * @psalm-return list{0?: string,...}
     */
    public function generate(array $parameters): array
    {
        $formattedParameters = [];
        foreach ($parameters as $parameter) {
            $formattedParameters[] = $this->translateableTextParameterHandler->handle($parameter);
        }

        return $formattedParameters;
    }
}
