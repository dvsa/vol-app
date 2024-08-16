<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\ApplicationAnswersClearer;

class ExistingIrhpPermitApplicationHandler
{
    /**
     * Create service instance
     *
     *
     * @return ExistingIrhpPermitApplicationHandler
     */
    public function __construct(private readonly IrhpPermitApplicationRepository $irhpPermitApplicationRepo, private readonly IrhpPermitStockRepository $irhpPermitStockRepo, private readonly ApplicationAnswersClearer $applicationAnswersClearer, private readonly QuestionHandlerDelegator $questionHandlerDelegator)
    {
    }

    /**
     * Handle the scenario where an irhp permit application already exists for a country
     *
     * @param int $stockId
     * @param array $requiredPermits
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function handle(IrhpPermitApplication $irhpPermitApplication, $stockId, $requiredPermits)
    {
        $existingStockId = $irhpPermitApplication->getIrhpPermitWindow()->getIrhpPermitStock()->getId();
        if ($stockId != $existingStockId) {
            $this->applicationAnswersClearer->clear($irhpPermitApplication);

            $irhpPermitStock = $this->irhpPermitStockRepo->fetchById($stockId);
            $irhpPermitApplication->updateIrhpPermitWindow(
                $irhpPermitStock->getOpenWindow()
            );
        }

        $applicationSteps = $irhpPermitApplication->getActiveApplicationPath()->getApplicationSteps();
        foreach ($applicationSteps as $applicationStep) {
            $this->questionHandlerDelegator->delegate(
                $irhpPermitApplication,
                $applicationStep,
                $requiredPermits
            );
        }

        $irhpPermitApplication->updateCheckAnswers();
        $this->irhpPermitApplicationRepo->save($irhpPermitApplication);
    }
}
