<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\StandardAndCabotageUpdater;
use Dvsa\Olcs\Api\Service\Qa\QaContext;

class StandardAndCabotageQuestionHandler implements QuestionHandlerInterface
{
    /**
     * Create service instance
     *
     *
     * @return StandardAndCabotageQuestionHandler
     */
    public function __construct(private readonly PermitUsageSelectionGenerator $permitUsageSelectionGenerator, private readonly BilateralRequiredGenerator $bilateralRequiredGenerator, private readonly StandardAndCabotageUpdater $standardAndCabotageUpdater)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QaContext $qaContext, array $requiredPermits)
    {
        $permitUsageSelection = $this->permitUsageSelectionGenerator->generate($requiredPermits);
        $bilateralRequired = $this->bilateralRequiredGenerator->generate($requiredPermits, $permitUsageSelection);

        $requiredStandard = $bilateralRequired[IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED];
        $requiredCabotage = $bilateralRequired[IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED];

        if ($requiredStandard && $requiredCabotage) {
            $answer = Answer::BILATERAL_STANDARD_AND_CABOTAGE;
        } elseif ($requiredStandard) {
            $answer = Answer::BILATERAL_STANDARD_ONLY;
        } else {
            $answer = Answer::BILATERAL_CABOTAGE_ONLY;
        }

        $this->standardAndCabotageUpdater->update($qaContext, $answer);
    }
}
