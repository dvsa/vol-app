<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionText;

class SelfservePage
{
    /**
     * Create instance
     *
     * @param string $title
     * @param string $submitOptionsName
     * @param string $nextStepSlug
     *
     * @return SelfservePage
     */
    public function __construct(private $title, private readonly array $additionalViewData, private readonly ApplicationStep $applicationStep, private readonly QuestionText $questionText, private $submitOptionsName, private $nextStepSlug)
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
            'title' => $this->title,
            'additionalViewData' => $this->additionalViewData,
            'applicationStep' => $this->applicationStep->getRepresentation(),
            'questionText' => $this->questionText->getRepresentation(),
            'submitOptions' => $this->submitOptionsName,
            'nextStepSlug' => $this->nextStepSlug,
        ];
    }

    /**
     * Get the embedded QuestionText instance
     *
     * @return QuestionText
     */
    public function getQuestionText()
    {
        return $this->questionText;
    }
}
