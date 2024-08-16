<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\NoOfPermitsConditionalUpdater;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerSaverInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsAvailableTextboxes as AvailableTextboxes;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\NamedAnswerFetcher;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpPermitApplicationOnlyTrait;

class NoOfPermitsAnswerSaver implements AnswerSaverInterface
{
    use IrhpPermitApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsAnswerSaver
     */
    public function __construct(private NamedAnswerFetcher $namedAnswerFetcher, private NoOfPermitsConditionalUpdater $noOfPermitsConditionalUpdater)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function save(QaContext $qaContext, array $postData)
    {
        $irhpPermitApplication = $qaContext->getQaEntity();
        $updatedAnswers = IrhpPermitApplication::DEFAULT_BILATERAL_REQUIRED;

        $cabotageAnswer = $irhpPermitApplication->getBilateralCabotageSelection();
        $availableTextboxes = AvailableTextBoxes::LOOKUP[$cabotageAnswer];
        $applicationStep = $qaContext->getApplicationStepEntity();

        foreach ($availableTextboxes as $standardOrCabotageKey) {
            $answerValue = $this->namedAnswerFetcher->fetch($applicationStep, $postData, $standardOrCabotageKey);
            $updatedAnswers[$standardOrCabotageKey] = $answerValue;
        }

        $this->noOfPermitsConditionalUpdater->update($irhpPermitApplication, $updatedAnswers);
    }
}
