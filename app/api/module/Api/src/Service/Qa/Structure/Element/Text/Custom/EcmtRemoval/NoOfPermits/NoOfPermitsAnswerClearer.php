<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerClearerInterface;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpApplicationOnlyTrait;

class NoOfPermitsAnswerClearer implements AnswerClearerInterface
{
    use IrhpApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsAnswerClearer
     */
    public function __construct(private IrhpPermitApplicationRepository $irhpPermitApplicationRepo)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function clear(QaContext $qaContext)
    {
        $irhpPermitApplication = $qaContext->getQaEntity()->getFirstIrhpPermitApplication();
        $irhpPermitApplication->clearPermitsRequired();
        $this->irhpPermitApplicationRepo->save($irhpPermitApplication);
    }
}
