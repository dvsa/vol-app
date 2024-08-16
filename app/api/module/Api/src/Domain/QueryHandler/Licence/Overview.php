<?php

/**
 * Licence Overview
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\LicenceStatusAwareTrait;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Domain\Repository\BusRegSearchView as BusRegSearchViewRepository;
use Dvsa\Olcs\Transfer\Query\Bus\SearchViewList as SearchViewListQuery;

/**
 * Licence Overview
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Overview extends AbstractQueryHandler
{
    use LicenceStatusAwareTrait;

    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['Application', 'TrafficArea', 'BusRegSearchView'];

    public function handleQuery(QueryInterface $query)
    {
        /** @var LicenceEntity $licence */
        $licence = $this->getRepo()->fetchUsingId($query);

        $discCriteria = Criteria::create();
        $discCriteria
            ->where($discCriteria->expr()->isNull('ceasedDate'));

        $applications = $this->getOtherApplicationsFromLicence($licence);
        $trafficAreas = $this->getRepo('TrafficArea')->getValueOptions();

        return $this->result(
            $licence,
            [
                'licenceType',
                'status',
                'goodsOrPsv',
                'organisation' => [
                    'leadTcArea',
                    'organisationUsers'
                ],
                'psvDiscs' => [
                    'criteria' => $discCriteria,
                ],
                'operatingCentres',
                'changeOfEntitys',
                'trafficArea',
                'gracePeriods',
            ],
            [
                'busCount' => $this->getBusRegCount($licence),
                'currentApplications' => $this->resultList($applications),
                'openCases' => $this->resultList($licence->getOpenCases(), ['publicInquiry']),
                'tradingName' => $licence->getTradingName(),
                'complaintsCount' => $licence->getOpenComplaintsCount(),
                // extra data needed to populate select boxes
                'valueOptions' => [
                    'trafficAreas' => $trafficAreas,
                ],
                'organisationLicenceCount' => $licence->getOrganisation()->getActiveLicences()->count(),
                'numberOfVehicles' => $licence->getActiveVehicles()->count(),
                'firstApplicationId' => $licence->getFirstApplicationId(),
                'applicableAuthProperties' => $licence->getApplicableAuthProperties(),
            ]
        );
    }

    protected function getOtherApplicationsFromLicence($licence)
    {
        $organisationId = $licence->getOrganisation()->getId();
        return $this->getRepo('Application')->fetchActiveForOrganisation($organisationId);
    }

    protected function getBusRegCount($licence)
    {
        // Here we get the bus reg list - all we need is a count...

        // BusRegSearchView query object
        $busRegSearchViewParams = [
            'licId' => $licence->getId(),
            'page' => 1,
            'sort' => 'regNo',
            'order' => 'DESC',
            'limit' => 10
        ];
        $query = SearchViewListQuery::create($busRegSearchViewParams);
        /** @var BusRegSearchViewRepository $busRegSearchViewRepo */
        $busRegSearchViewRepo = $this->getRepo('BusRegSearchView');

        return $busRegSearchViewRepo->fetchCount($query);
    }
}
