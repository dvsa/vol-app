<?php

namespace Dvsa\Olcs\Api\Service\Permits\AnswersSummary;

class AnswersSummaryRow
{
    /**
     * Create instance
     *
     * @param string $question
     * @param string $formattedAnswer
     * @param string|null $slug
     *
     * @return AnswersSummaryRow
     */
    public function __construct(private $question, private $formattedAnswer, private $slug = null)
    {
    }

    /**
     * Get the representation of this class to be returned by the API endpoint
     *
     * @return array
     */
    public function getRepresentation()
    {
        return [
            'question' => $this->question,
            'answer' => $this->formattedAnswer,
            'slug' => $this->slug,
        ];
    }
}
