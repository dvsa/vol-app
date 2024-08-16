<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Domain\FormControlServiceManager;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContextGenerator;

class ApplicationStepGenerator
{
    /**
     * Create service instance
     *
     *
     * @return ApplicationStepGenerator
     */
    public function __construct(private readonly FormControlServiceManager $formControlServiceManager, private readonly ApplicationStepFactory $applicationStepFactory, private readonly ElementGeneratorContextGenerator $elementGeneratorContextGenerator)
    {
    }

    /**
     * Build and return an ApplicationStep instance using the appropriate data sources
     *
     * @param string $elementContainer
     * @return ApplicationStep
     */
    public function generate(QaContext $qaContext, $elementContainer)
    {
        $applicationStepEntity = $qaContext->getApplicationStepEntity();

        $formControlStrategy = $this->formControlServiceManager->getByApplicationStep($applicationStepEntity);
        $elementGeneratorContext = $this->elementGeneratorContextGenerator->generate($qaContext, $elementContainer);
        $element = $formControlStrategy->getElement($elementGeneratorContext);
        $question = $applicationStepEntity->getQuestion();

        return $this->applicationStepFactory->create(
            $formControlStrategy->getFrontendType(),
            $applicationStepEntity->getFieldsetName(),
            $question->getActiveQuestionText()->getQuestionShortKey(),
            $question->getSlug(),
            $qaContext->isApplicationStepEnabled(),
            $element,
            $elementGeneratorContext->getValidatorList()
        );
    }
}
