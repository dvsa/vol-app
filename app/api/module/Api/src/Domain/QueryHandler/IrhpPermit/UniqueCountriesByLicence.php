<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermit;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByLicence;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class UniqueCountriesByLicence extends AbstractQueryHandler
{
    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        if ($query->getIrhpPermitType() != IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL) {
            return [];
        }

        $getListByLicenceQuery = GetListByLicence::create(
            $query->getArrayCopy()
        );

        $response = $this->getQueryHandler()->handleQuery($getListByLicenceQuery, false);

        $countryMap = [];
        foreach ($response['results'] as $irhpPermit) {
            $country = $irhpPermit['irhpPermitRange']['irhpPermitStock']['country'];

            $countryId = $country['id'];
            $countryName = $country['countryDesc'];
            $countryMap[$countryId] = $countryName;
        }

        asort($countryMap);

        return $countryMap;
    }
}
