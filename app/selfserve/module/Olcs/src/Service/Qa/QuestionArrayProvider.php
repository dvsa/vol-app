<?php

namespace Olcs\Service\Qa;

use Common\Service\Qa\FormattedTranslateableTextParametersGenerator;
use RuntimeException;

class QuestionArrayProvider
{
    public const ONLY_HTML_ESCAPE_SUPPORTED = 'Selfserve only currently supports the htmlEscape filter for question text';

    /**
     * Create service instance
     *
     *
     * @return QuestionArrayProvider
     */
    public function __construct(private FormattedTranslateableTextParametersGenerator $formattedTranslateableTextParametersGenerator)
    {
    }

    /**
     * Get the base template variables corresponding to the provided question text data
     *
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
