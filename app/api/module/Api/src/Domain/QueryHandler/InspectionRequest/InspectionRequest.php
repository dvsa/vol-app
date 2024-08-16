<?php

/**
 * Inspection Request
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\InspectionRequest;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\InspectionRequest as InspectionRequestRepo;

/**
 * Inspection Request
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InspectionRequest extends AbstractQueryHandler
{
    protected $repoServiceName = 'InspectionRequest';

    public function handleQuery(QueryInterface $query)
    {
        /** @var InspectionRequestRepo $repo */
        $repo = $this->getRepo();
        $inspectionRequest = $repo->fetchUsingId($query);

        return $this->result(
            $inspectionRequest,
            [
                'reportType',
                'requestType',
                'resultType',
                'application' => [
                    'licenceType',
                    'operatingCentres' => [
                        'operatingCentre' => [
                            'address'
                        ],
                    ],
                    'licence' => [
                        'enforcementArea'
                    ]
                ],
                'licence' => [
                    'enforcementArea',
                    'licenceType',
                    'organisation' => [
                        'tradingNames',
                        'licences',
                    ],
                    'operatingCentres',
                    'correspondenceCd' => [
                        'address' => [],
                        'phoneContacts' => [
                            'phoneContactType',
                        ],
                    ],
                    'tmLicences' => [
                        'transportManager' => [
                            'homeCd' => [
                                'person',
                            ],
                        ],
                    ],
                ],
                'operatingCentre' => [
                    'address'
                ],
            ]
        );
    }
}
