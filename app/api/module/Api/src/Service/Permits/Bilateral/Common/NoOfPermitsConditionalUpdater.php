<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Common;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;

class NoOfPermitsConditionalUpdater
{
    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsConditionalUpdater
     */
    public function __construct(private readonly NoOfPermitsUpdater $noOfPermitsUpdater)
    {
    }

    /**
     * Update the number of permits answer and fees relating to a specific country within a bilateral application, but
     * only if the number of permits answer has actually changed
     */
    public function update(IrhpPermitApplication $irhpPermitApplication, array $updatedAnswers)
    {
        $existingAnswers = $irhpPermitApplication->getBilateralRequired();

        if ($updatedAnswers == $existingAnswers) {
            return;
        }

        $this->noOfPermitsUpdater->update($irhpPermitApplication, $updatedAnswers);
    }
}
