<?php

namespace Permits\View\Helper;

use Common\RefData;
use DateTime;
use Zend\View\Helper\AbstractHelper;

/**
 * Format data passed in q&a format
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class AnswerFormatter extends AbstractHelper
{
    const SEPARATOR = '<br/>';

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

        foreach ($data['answer'] as $answer) {
            switch ($data['questionType']) {
                case RefData::QUESTION_TYPE_BOOLEAN:
                    $answers[] = $this->translateAndEscape(
                        $this->formatBoolean($answer),
                        $data['escape']
                    );
                    break;
                case RefData::QUESTION_TYPE_INTEGER:
                    $answers[] = (int)$answer;
                    break;
                default:
                    // TODO: this isn't ideal but will be resolved by the upcoming check answers and
                    // snapshot refactor
                    $dateQuestions = [
                        'qanda.ecmt-removal.permit-start-date.question',
                        'qanda.certificate-of-roadworthiness.vehicle.mot-expiry-date.question',
                        'qanda.certificate-of-roadworthiness.trailer.mot-expiry-date.question',
                        'qanda.ecmt-short-term.earliest-permit-date.question',
                    ];

                    if ($data['question'] == 'qanda.common.certificates.question') {
                        $answer = $this->formatBoolean($answer);
                    } elseif (in_array($data['question'], $dateQuestions)) {
                        $answer = $this->formatDate($answer);
                    }

                    $answers[] = $this->translateAndEscape($answer, $data['escape']);
            }
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
     * @param mixed $answer
     *
     * @return string
     */
    private function formatBoolean($answer)
    {
        if (!$answer) {
            return 'No';
        }

        return 'Yes';
    }

    /**
     * Format a date value
     *
     * @param string $answer
     *
     * @return string
     */
    private function formatDate($answer)
    {
        $dateTime = new DateTime($answer);

        return $dateTime->format('d/m/Y');
    }
}
