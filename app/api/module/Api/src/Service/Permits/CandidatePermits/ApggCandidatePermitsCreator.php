<?php

namespace Dvsa\Olcs\Api\Service\Permits\CandidatePermits;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;

class ApggCandidatePermitsCreator
{
    /**
     * Create service instance
     *
     *
     * @return ApggCandidatePermitsCreator
     */
    public function __construct(private readonly ApggEmissionsCatCandidatePermitsCreator $apggEmissionsCatCandidatePermitsCreator)
    {
    }

    /**
     * Create apgg candidate permits as required for the specified application
     */
    public function create(IrhpApplication $irhpApplication)
    {
        $emissionsCategoryIds = [RefData::EMISSIONS_CATEGORY_EURO5_REF, RefData::EMISSIONS_CATEGORY_EURO6_REF];

        foreach ($emissionsCategoryIds as $emissionsCategoryId) {
            $this->apggEmissionsCatCandidatePermitsCreator->createIfRequired($irhpApplication, $emissionsCategoryId);
        }
    }
}
