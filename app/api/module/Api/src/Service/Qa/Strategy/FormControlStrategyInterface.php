<?php

namespace Dvsa\Olcs\Api\Service\Qa\Strategy;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AnswerSummaryProviderInterface;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionText;

interface FormControlStrategyInterface
{
    /**
     * Get the name used by the frontend to render this form control
     *
     * @return string
     */
    public function getFrontendType();

    /**
     * Get an instance of ElementInterface representing this form control
     *
     *
     * @return ElementInterface
     */
    public function getElement(ElementGeneratorContext $context);

    /**
     * Save the data for this form control to the persistent data store
     *
     *
     * @return string
     */
    public function saveFormData(QaContext $qaContext, array $postData);

    /**
     * Remove or reset to the default state any answer present for this form control
     */
    public function clearAnswer(QaContext $qaContext);

    /**
     * Get a QuestionText instance corresponding to this form control
     *
     *
     * @return QuestionText
     */
    public function getQuestionText(QaContext $qaContext);

    /**
     * Get the appropriate instance of AnswerSummaryProviderInterface for this form control
     *
     * @return AnswerSummaryProviderInterface
     */
    public function getAnswerSummaryProvider();
}
