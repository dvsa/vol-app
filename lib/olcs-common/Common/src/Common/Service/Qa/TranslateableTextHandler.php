<?php

namespace Common\Service\Qa;

use Common\Service\Helper\TranslationHelperService;

class TranslateableTextHandler
{
    /**
     * Create service instance
     *
     *
     * @return TranslateableTextHandler
     */
    public function __construct(private FormattedTranslateableTextParametersGenerator $formattedTranslateableTextParametersGenerator, private TranslationHelperService $translationHelper)
    {
    }

    /**
     * Derive a translated string from a translatable text array representation
     *
     *
     * @return string
     */
    public function translate(array $translateableText)
    {
        return $this->translationHelper->translateReplace(
            $translateableText['key'],
            $this->formattedTranslateableTextParametersGenerator->generate($translateableText['parameters'])
        );
    }
}
