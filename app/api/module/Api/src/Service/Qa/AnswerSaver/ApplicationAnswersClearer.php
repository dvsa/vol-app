<?php

namespace Dvsa\Olcs\Api\Service\Qa\AnswerSaver;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\Facade\SupplementedApplicationSteps\SupplementedApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\Facade\SupplementedApplicationSteps\SupplementedApplicationStepsProvider;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaContextFactory;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use RuntimeException;

class ApplicationAnswersClearer
{
    /**
     * Create service instance
     *
     *
     * @return ApplicationAnswersClearer
     */
    public function __construct(private readonly SupplementedApplicationStepsProvider $supplementedApplicationStepsProvider, private readonly QaContextFactory $qaContextFactory)
    {
    }

    /**
     * Remove or reset to the default state all answers for this application
     */
    public function clear(QaEntityInterface $qaEntity)
    {
        $supplementedApplicationSteps = $this->supplementedApplicationStepsProvider->get($qaEntity);

        foreach ($supplementedApplicationSteps as $supplementedApplicationStep) {
            $this->clearAnswer($qaEntity, $supplementedApplicationStep);
        }
    }

    /**
     * Remove or reset to the default state all answers for this application that follow the application step
     * contained in the specified qa entity
     *
     *
     * @throws RuntimeException
     */
    public function clearAfterApplicationStep(QaContext $qaContext)
    {
        $qaEntity = $qaContext->getQaEntity();
        $applicationStep = $qaContext->getApplicationStepEntity();

        $supplementedApplicationSteps = $this->supplementedApplicationStepsProvider->get($qaEntity);
        $afterApplicationStep = false;

        foreach ($supplementedApplicationSteps as $supplementedApplicationStep) {
            if ($afterApplicationStep) {
                $this->clearAnswer($qaEntity, $supplementedApplicationStep);
            }

            if ($supplementedApplicationStep->getApplicationStep() === $applicationStep) {
                $afterApplicationStep = true;
            }
        }

        if (!$afterApplicationStep) {
            $exceptionMessage = sprintf(
                'application step with id %s was not found in application steps',
                $applicationStep->getId()
            );

            throw new RuntimeException($exceptionMessage);
        }
    }

    /**
     * Clear the answer corresponding to the specified qa entity and supplemented application step
     */
    private function clearAnswer(QaEntityInterface $qaEntity, SupplementedApplicationStep $supplementedApplicationStep)
    {
        $qaContext = $this->qaContextFactory->create(
            $supplementedApplicationStep->getApplicationStep(),
            $qaEntity
        );

        $supplementedApplicationStep->getFormControlStrategy()->clearAnswer($qaContext);
    }
}
