<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepository;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerSaverInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerFetcher;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpApplicationOnlyTrait;

class IntJourneysAnswerSaver implements AnswerSaverInterface
{
    use IrhpApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return IntJourneysAnswerSaver
     */
    public function __construct(private IrhpApplicationRepository $irhpApplicationRepo, private GenericAnswerFetcher $genericAnswerFetcher)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function save(QaContext $qaContext, array $postData)
    {
        $answer = $this->genericAnswerFetcher->fetch(
            $qaContext->getApplicationStepEntity(),
            $postData
        );

        $irhpApplicationEntity = $qaContext->getQaEntity();

        $irhpApplicationEntity->updateInternationalJourneys(
            $this->irhpApplicationRepo->getRefdataReference($answer)
        );

        $this->irhpApplicationRepo->save($irhpApplicationEntity);
    }
}
