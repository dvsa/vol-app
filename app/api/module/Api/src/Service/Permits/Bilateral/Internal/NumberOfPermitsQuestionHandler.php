<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\NoOfPermitsConditionalUpdater;
use Dvsa\Olcs\Api\Service\Qa\QaContext;

class NumberOfPermitsQuestionHandler implements QuestionHandlerInterface
{
    /**
     * Create service instance
     *
     *
     * @return NumberOfPermitsQuestionHandler
     */
    public function __construct(private readonly PermitUsageSelectionGenerator $permitUsageSelectionGenerator, private readonly BilateralRequiredGenerator $bilateralRequiredGenerator, private readonly NoOfPermitsConditionalUpdater $noOfPermitsConditionalUpdater)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QaContext $qaContext, array $requiredPermits)
    {
        $permitUsageSelection = $this->permitUsageSelectionGenerator->generate($requiredPermits);
        $bilateralRequired = $this->bilateralRequiredGenerator->generate($requiredPermits, $permitUsageSelection);

        $this->noOfPermitsConditionalUpdater->update(
            $qaContext->getQaEntity(),
            $bilateralRequired
        );
    }
}
