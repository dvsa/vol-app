<?php

/**
 * Bus
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bus;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Bus
 */
class Bus extends AbstractQueryHandler
{
    protected $repoServiceName = 'Bus';

    /**
     * Handle query
     *
     * @param QueryInterface $query Query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var BusReg $busReg */
        $busReg = $this->getRepo()->fetchUsingId($query);

        $this->auditRead($busReg);

        return $this->result(
            $busReg,
            [
                'licence' => [
                    'organisation' => $this->getOrganisationResultsBundle(),
                    'licenceType',
                    'status',
                ],
                'busNoticePeriod',
                'busServiceTypes',
                'trafficAreas',
                'localAuthoritys',
                'subsidised',
                'otherServices',
                'variationReasons'
            ]
        );
    }

    /**
     * Determine the organisation results bundle
     *
     * @return array
     */
    private function getOrganisationResultsBundle()
    {
        if ($this->getCurrentUser()->isAnonymous()) {
            return [];
        }

        return ['disqualifications'];
    }
}
