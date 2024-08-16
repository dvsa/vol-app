<?php

namespace Dvsa\Olcs\Snapshot\View\Helper;

use Dvsa\Olcs\Api\Entity\Generic\Question;
use Laminas\View\Helper\AbstractHelper;

/**
 * Format data passed in q&a format
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class AnswerFormatter extends AbstractHelper
{
    public const SEPARATOR = '<br/>';

    /**
     * Expects a Q&A answer in the form of $data['question'], $data['answer'] etc
     * Basic for now, will return a formatted/translated answer based on the question type
     * For future, add support for external formatters
     *
     * @param array $data answer data
     *
     * @return string
     */
    public function __invoke(array $data): string
    {
        $answers = [];

        if (!is_array($data['answer'])) {
            $data['answer'] = (array)$data['answer'];
        }

        $escape = $data['escape'] ?? true;

        foreach ($data['answer'] as $answer) {
            $answers[] = match ($data['questionType']) {
                Question::QUESTION_TYPE_BOOLEAN => $this->translateAndEscape(
                    $this->formatBoolean($answer),
                    $escape
                ),
                Question::QUESTION_TYPE_INTEGER => (int) $answer,
                default => $this->translateAndEscape($answer, $escape),
            };
        }

        return implode(self::SEPARATOR, $answers);
    }

    /**
     * Translate and escape the answer
     *
     * @param string $answer the answer
     * @param bool   $escape the answer
     *
     * @return string
     */
    private function translateAndEscape($answer, bool $escape): string
    {
        $translatedAnswer = $this->view->translate($answer);

        if ($escape) {
            return $this->view->escapeHtml($translatedAnswer);
        }

        return $translatedAnswer;
    }

    /**
     * Format a truthy/falsy value as a string value of Yes or No
     *
     *
     * @return string
     */
    private function formatBoolean(mixed $answer)
    {
        if (!$answer) {
            return 'No';
        }

        return 'Yes';
    }
}
