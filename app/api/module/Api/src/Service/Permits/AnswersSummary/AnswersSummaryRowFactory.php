<?php

namespace Dvsa\Olcs\Api\Service\Permits\AnswersSummary;

class AnswersSummaryRowFactory
{
    /**
     * Create and return a AnswersSummaryRow instance
     *
     * @param string $question
     * @param string $formattedAnswer
     * @param string|null $slug
     *
     * @return AnswersSummaryRow
     */
    public function create($question, $formattedAnswer, $slug = null)
    {
        return new AnswersSummaryRow($question, $formattedAnswer, $slug);
    }
}
