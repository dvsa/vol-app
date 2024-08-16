<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\ApplicationFeesClearer;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerClearerInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpPermitApplicationOnlyTrait;

class NoOfPermitsAnswerClearer implements AnswerClearerInterface
{
    use IrhpPermitApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsAnswerClearer
     */
    public function __construct(private ApplicationFeesClearer $applicationFeesClearer, private IrhpPermitApplicationRepository $irhpPermitApplicationRepo)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function clear(QaContext $qaContext)
    {
        $irhpPermitApplication = $qaContext->getQaEntity();

        $this->applicationFeesClearer->clear($irhpPermitApplication);

        $irhpPermitApplication->clearBilateralRequired();
        $this->irhpPermitApplicationRepo->save($irhpPermitApplication);
    }
}
