<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;

class AnswerWriter
{
    /**
     * Create service instance
     *
     *
     * @return AnswerWriter
     */
    public function __construct(private readonly IrhpPermitApplicationRepository $irhpPermitApplicationRepo)
    {
    }

    /**
     * Write the number of permits required to persistent storage
     *
     * @param int $permitsRequired
     */
    public function write(IrhpApplicationEntity $irhpApplication, $permitsRequired)
    {
        $irhpPermitApplication = $irhpApplication->getFirstIrhpPermitApplication();
        $irhpPermitApplication->updatePermitsRequired($permitsRequired);
        $this->irhpPermitApplicationRepo->save($irhpPermitApplication);
    }
}
