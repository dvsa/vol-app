<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText;

use Dvsa\Olcs\Api\Service\Qa\Structure\JsonDecodingFilteredTranslateableTextGenerator;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Supports\AnyTrait;

class QuestionTextGenerator implements QuestionTextGeneratorInterface
{
    use AnyTrait;

    /**
     * Create service instance
     *
     *
     * @return QuestionTextGenerator
     */
    public function __construct(private QuestionTextFactory $questionTextFactory, private JsonDecodingFilteredTranslateableTextGenerator $jsonDecodingFilteredTranslateableTextGenerator)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(QaContext $qaContext)
    {
        $questionTextEntity = $qaContext->getApplicationStepEntity()->getQuestion()->getActiveQuestionText();

        return $this->questionTextFactory->create(
            $this->jsonDecodingFilteredTranslateableTextGenerator->generate(
                $questionTextEntity->getQuestionKey()
            ),
            $this->jsonDecodingFilteredTranslateableTextGenerator->generate(
                $questionTextEntity->getQuestionSummaryKey()
            ),
            $this->jsonDecodingFilteredTranslateableTextGenerator->generate(
                $questionTextEntity->getDetailsKey()
            ),
            $this->jsonDecodingFilteredTranslateableTextGenerator->generate(
                $questionTextEntity->getGuidanceKey()
            ),
            $this->jsonDecodingFilteredTranslateableTextGenerator->generate(
                $questionTextEntity->getAdditionalGuidanceKey()
            )
        );
    }
}
