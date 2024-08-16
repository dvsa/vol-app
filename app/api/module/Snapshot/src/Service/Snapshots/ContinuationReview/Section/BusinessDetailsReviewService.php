<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Business Details Continuation Review Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class BusinessDetailsReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param ContinuationDetail $continuationDetail continuation detail
     *
     * @return array
     */
    public function getConfigFromData(ContinuationDetail $continuationDetail)
    {
        /** @var Licence $licence */
        $licence = $continuationDetail->getLicence();

        $organisation = $licence->getOrganisation();
        $organisationTypeId = $organisation->getType()->getId();

        $baseCompanyTypes = [
            Organisation::ORG_TYPE_REGISTERED_COMPANY,
            Organisation::ORG_TYPE_LLP,
            Organisation::ORG_TYPE_PARTNERSHIP,
            Organisation::ORG_TYPE_OTHER,
        ];
        $limitedCompanyTypes = [
            Organisation::ORG_TYPE_REGISTERED_COMPANY,
            Organisation::ORG_TYPE_LLP
        ];
        $organisationLabels = [
            Organisation::ORG_TYPE_REGISTERED_COMPANY => 'continuation-review-business-details-company_name',
            Organisation::ORG_TYPE_LLP => 'continuation-review-business-details-company_name',
            Organisation::ORG_TYPE_PARTNERSHIP => 'continuation-review-business-details-partnership_name',
            Organisation::ORG_TYPE_OTHER => 'continuation-review-business-details-organisation_name'
        ];

        $config = [];

        if (in_array($organisationTypeId, $limitedCompanyTypes)) {
            $config[] = [
                ['value' => 'continuation-review-business-details-company_number'],
                ['value' => $organisation->getCompanyOrLlpNo(), 'header' => true],
            ];
        }
        if (in_array($organisationTypeId, $baseCompanyTypes)) {
            $config[] = [
                ['value' => $organisationLabels[$organisationTypeId]],
                ['value' => $organisation->getName(), 'header' => true],
            ];
        }
        if ($organisationTypeId !== Organisation::ORG_TYPE_OTHER) {
            $tradingNames = $licence->getTradingNames();
            $tradingNamesAsString = $tradingNames->count() !== 0
                ? $this->getTradingNamesAsString($tradingNames)
                : 'continuation-review-business-details-trading_names_none_added';
            $config[] = [
                ['value' => 'continuation-review-business-details-trading_names'],
                ['value' => $tradingNamesAsString, 'header' => true],
            ];
        }

        return $config;
    }

    /**
     * Get trading names as string
     *
     * @param ArrayCollection $tradingNames trading names
     *
     * @return string
     */
    public function getTradingNamesAsString($tradingNames)
    {
        $retv = '';
        foreach ($tradingNames as $tradingName) {
            $retv .= $tradingName->getName() . ', ';
        }
        return substr($retv, 0, strlen($retv) - 2);
    }
}
