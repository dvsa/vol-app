<?php

namespace Dvsa\Olcs\Api\Service\Qa\PostSubmit;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

class IrhpApplicationPostSubmitHandler
{
    /**
     * Create service instance
     *
     *
     * @return IrhpApplicationPostSubmitHandler
     */
    public function __construct(private readonly IrhpPermitRepository $irhpPermitRepo)
    {
    }

    /**
     * Perform any required steps following the saving of the application
     */
    public function handle(IrhpApplication $irhpApplication)
    {
        if (!$irhpApplication->getIrhpPermitType()->isEcmtRemoval()) {
            return;
        }

        $irhpPermits = $irhpApplication->getFirstIrhpPermitApplication()->getIrhpPermits();
        foreach ($irhpPermits as $irhpPermit) {
            $irhpPermit->regenerateIssueDateAndExpiryDate();
            $this->irhpPermitRepo->save($irhpPermit);
        }
    }
}
