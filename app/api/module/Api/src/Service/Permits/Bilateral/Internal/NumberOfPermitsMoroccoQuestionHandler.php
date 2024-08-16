<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\NoOfPermitsConditionalUpdater;
use Dvsa\Olcs\Api\Service\Qa\QaContext;

class NumberOfPermitsMoroccoQuestionHandler implements QuestionHandlerInterface
{
    /**
     * Create service instance
     *
     *
     * @return NumberOfPermitsMoroccoQuestionHandler
     */
    public function __construct(private readonly NoOfPermitsConditionalUpdater $noOfPermitsConditionalUpdater)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QaContext $qaContext, array $requiredPermits)
    {
        $bilateralRequired = IrhpPermitApplication::DEFAULT_BILATERAL_REQUIRED;
        $bilateralRequired[IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED] = $requiredPermits['permitsRequired'];

        $this->noOfPermitsConditionalUpdater->update(
            $qaContext->getQaEntity(),
            $bilateralRequired
        );
    }
}
