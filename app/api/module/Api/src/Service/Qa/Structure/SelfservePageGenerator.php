<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Domain\FormControlServiceManager;
use Dvsa\Olcs\Api\Service\Qa\QaContext;

class SelfservePageGenerator
{
    /**
     * Create service instance
     *
     *
     * @return SelfservePageGenerator
     */
    public function __construct(private readonly SelfservePageFactory $selfservePageFactory, private readonly ApplicationStepGenerator $applicationStepGenerator, private readonly FormControlServiceManager $formControlServiceManager)
    {
    }

    /**
     * Build and return a Selfserve instance using the appropriate data sources
     *
     *
     * @return SelfservePage
     */
    public function generate(QaContext $qaContext)
    {
        $applicationStepEntity = $qaContext->getApplicationStepEntity();
        $qaEntity = $qaContext->getQaEntity();

        $formControlStrategy = $this->formControlServiceManager->getByApplicationStep($applicationStepEntity);
        $question = $applicationStepEntity->getQuestion();

        $selfservePage = $this->selfservePageFactory->create(
            $question->getActiveQuestionText()->getTranslationKeyFromQuestionKey(),
            $qaEntity->getAdditionalQaViewData($applicationStepEntity),
            $this->applicationStepGenerator->generate($qaContext, ElementContainer::SELFSERVE_PAGE),
            $formControlStrategy->getQuestionText($qaContext),
            $question->getSubmitOptions()->getId(),
            $applicationStepEntity->getNextStepSlug()
        );

        return $selfservePage;
    }
}
