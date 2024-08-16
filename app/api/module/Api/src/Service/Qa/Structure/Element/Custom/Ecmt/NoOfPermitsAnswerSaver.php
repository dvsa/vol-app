<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerSaverInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpApplicationOnlyTrait;

class NoOfPermitsAnswerSaver implements AnswerSaverInterface
{
    use IrhpApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsAnswerSaver
     */
    public function __construct(private IrhpPermitApplicationRepository $irhpPermitApplicationRepo, private NoOfPermitsAnswerFetcher $noOfPermitsAnswerFetcher, private ConditionalFeeUpdater $conditionalFeeUpdater)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function save(QaContext $qaContext, array $postData)
    {
        $applicationStepEntity = $qaContext->getApplicationStepEntity();
        $irhpApplicationEntity = $qaContext->getQaEntity();

        $requiredEuro5 = $this->noOfPermitsAnswerFetcher->fetch(
            $applicationStepEntity,
            $postData,
            FieldNames::REQUIRED_EURO5
        );

        $requiredEuro6 = $this->noOfPermitsAnswerFetcher->fetch(
            $applicationStepEntity,
            $postData,
            FieldNames::REQUIRED_EURO6
        );

        $irhpPermitApplication = $irhpApplicationEntity->getFirstIrhpPermitApplication();

        $oldTotal = $irhpPermitApplication->getTotalEmissionsCategoryPermitsRequired();
        $irhpPermitApplication->updateEmissionsCategoryPermitsRequired($requiredEuro5, $requiredEuro6);
        $this->irhpPermitApplicationRepo->save($irhpPermitApplication);

        $this->conditionalFeeUpdater->updateFees($irhpApplicationEntity, $oldTotal);
    }
}
