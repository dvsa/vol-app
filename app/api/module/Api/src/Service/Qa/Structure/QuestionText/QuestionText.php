<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText;

use Dvsa\Olcs\Api\Service\Qa\Structure\FilteredTranslateableText;

class QuestionText
{
    /**
     * Create instance
     *
     * @param FilteredTranslateableText $question (optional)
     * @param FilteredTranslateableText $questionSummary (optional)
     * @param FilteredTranslateableText $details (optional)
     * @param FilteredTranslateableText $guidance (optional)
     * @param FilteredTranslateableText $additionalGuidance (optional)
     *
     * @return QuestionText
     */
    public function __construct(private readonly ?FilteredTranslateableText $question = null, private readonly ?FilteredTranslateableText $questionSummary = null, private readonly ?FilteredTranslateableText $details = null, private readonly ?FilteredTranslateableText $guidance = null, private readonly ?FilteredTranslateableText $additionalGuidance = null)
    {
    }

    /**
     * Get the representation of this class to be returned by the API endpoint
     *
     * @return array
     */
    public function getRepresentation()
    {
        if (!is_null($this->question)) {
            $response['question'] = $this->question->getRepresentation();
        }

        if (!is_null($this->details)) {
            $response['details'] = $this->details->getRepresentation();
        }

        if (!is_null($this->guidance)) {
            $response['guidance'] = $this->guidance->getRepresentation();
        }

        if (!is_null($this->additionalGuidance)) {
            $response['additionalGuidance'] = $this->additionalGuidance->getRepresentation();
        }

        return $response;
    }

    /**
     * Get the embedded FilteredTranslateableText instance representing the question
     *
     * @return FilteredTranslateableText|null
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Get the embedded FilteredTranslateableText instance representing the question summary
     *
     * @return FilteredTranslateableText|null
     */
    public function getQuestionSummary()
    {
        return $this->questionSummary;
    }

    /**
     * Get the embedded FilteredTranslateableText instance representing the guidance
     *
     * @return FilteredTranslateableText|null
     */
    public function getGuidance()
    {
        return $this->guidance;
    }

    /**
     * Get the embedded FilteredTranslateableText instance representing the additional guidance
     *
     * @return FilteredTranslateableText|null
     */
    public function getAdditionalGuidance()
    {
        return $this->additionalGuidance;
    }
}
