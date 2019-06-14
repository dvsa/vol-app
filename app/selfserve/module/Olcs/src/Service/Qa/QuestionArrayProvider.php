<?php

namespace Olcs\Service\Qa;

use Common\Service\Qa\FormattedTranslateableTextParametersGenerator;
use RuntimeException;

class QuestionArrayProvider
{
    const ONLY_HTML_ESCAPE_SUPPORTED = 'Selfserve only currently supports the htmlEscape filter for question text';

    /** @var FormattedTranslateableTextParametersGenerator */
    private $formattedTranslateableTextParametersGenerator;

    /**
     * Create service instance
     *
     * @param FormattedTranslateableTextParametersGenerator $formattedTranslateableTextParametersGenerator
     *
     * @return QuestionArrayProvider
     */
    public function __construct(
        FormattedTranslateableTextParametersGenerator $formattedTranslateableTextParametersGenerator
    ) {
        $this->formattedTranslateableTextParametersGenerator = $formattedTranslateableTextParametersGenerator;
    }

    /**
     * Get the base template variables corresponding to the provided question text data
     *
     * @param array $question
     *
     * @return array
     */
    public function get(array $question)
    {
        if ($question['filter'] != 'htmlEscape') {
            throw new RuntimeException(self::ONLY_HTML_ESCAPE_SUPPORTED);
        }

        $translateableText = $question['translateableText'];

        $questionArgs = $this->formattedTranslateableTextParametersGenerator->generate(
            $translateableText['parameters']
        );

        return [
            'question' => $translateableText['key'],
            'questionArgs' => $questionArgs
        ];
    }
}
