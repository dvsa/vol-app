<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\StandardAndCabotageUpdater;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerSaverInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\NamedAnswerFetcher;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpPermitApplicationOnlyTrait;

class StandardAndCabotageAnswerSaver implements AnswerSaverInterface
{
    use IrhpPermitApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return StandardAndCabotageAnswerSaver
     */
    public function __construct(private NamedAnswerFetcher $namedAnswerFetcher, private StandardAndCabotageUpdater $standardAndCabotageUpdater)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function save(QaContext $qaContext, array $postData)
    {
        $applicationStepEntity = $qaContext->getApplicationStepEntity();

        $cabotageRequired = $this->namedAnswerFetcher->fetch(
            $applicationStepEntity,
            $postData,
            'qaElement'
        );

        $newAnswer = Answer::BILATERAL_STANDARD_ONLY;
        if ($cabotageRequired == 'Y') {
            $newAnswer = $this->namedAnswerFetcher->fetch(
                $applicationStepEntity,
                $postData,
                'yesContent'
            );
        }

        $this->standardAndCabotageUpdater->update($qaContext, $newAnswer);
    }
}
