<?php

namespace Olcs\Service\Qa;

use RuntimeException;

class QuestionArrayProvider
{
    const ONLY_HTML_ESCAPE_SUPPORTED = 'Selfserve only currently supports the htmlEscape filter for question text';

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

        return [
            'question' => $translateableText['key'],
            'questionArgs' => $translateableText['parameters']
        ];
    }
}
