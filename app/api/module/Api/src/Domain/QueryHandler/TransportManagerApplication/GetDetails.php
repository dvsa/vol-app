<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Tm\TmQualification;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Get a Transport Manager Application
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetDetails extends AbstractQueryHandler
{
    protected $repoServiceName = 'TransportManagerApplication';

    protected $extraRepos = [
        'ApplicationOperatingCentre',
        'LicenceOperatingCentre',
        'PreviousConviction',
        'OtherLicence',
        'TmEmployment',
        'SystemParameter',
    ];

    /**
     * Handle Query
     *
     * @param \Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetDetails $query Query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication $repo */
        $repo = $this->getRepo();

        /* @var $tma TransportManagerApplication */
        $tma = $repo->fetchDetails($query->getId());

        $tmId = $tma->getTransportManager()->getId();

        // get LGV Acquired Rights reference number
        $lgvAcquiredRightsQualification = $tma->getTransportManager()->getLgvAcquiredRightsQualification();

        $lgvAcquiredRightsReferenceNumber = ($lgvAcquiredRightsQualification instanceof TmQualification)
            ? $lgvAcquiredRightsQualification->getSerialNo() : '';

        // populate the required associated entities
        $this->getRepo('ApplicationOperatingCentre')->fetchByApplication($tma->getApplication()->getId());
        $this->getRepo('LicenceOperatingCentre')->fetchByLicence($tma->getApplication()->getLicence()->getId());
        $this->getRepo('PreviousConviction')->fetchByTransportManager($tmId);
        $this->getRepo('OtherLicence')->fetchByTransportManager($tmId);
        $this->getRepo('TmEmployment')->fetchByTransportManager($tmId);

        return $this->result(
            $tma,
            [
                'application' => [
                    'licence' => [
                        'operatingCentres' => [
                            'operatingCentre' => [
                                'address' => [
                                    'countryCode'
                                ]
                            ]
                        ],
                        'organisation',
                    ],
                    'operatingCentres' => [
                        'operatingCentre' => [
                            'address' => [
                                'countryCode'
                            ]
                        ]
                    ],
                    'vehicleType',
                ],
                'transportManager' => [
                    'homeCd' => [
                        'address' => [
                            'countryCode'
                        ],
                        'person'
                    ],
                    'workCd' => [
                        'address' => [
                            'countryCode'
                        ],
                        'person'
                    ],
                    'otherLicences',
                    'previousConvictions',
                    'employments' => [
                        'contactDetails' => [
                            'address' => [
                                'countryCode'
                            ],
                            'person'
                        ]
                    ],
                    'documents' => [
                        'application',
                        'category',
                        'subCategory',
                    ],
                ],
                'tmDigitalSignature',
                'opDigitalSignature',
                'otherLicences'
            ],
            [
                'isTmLoggedInUser' => $this->getCurrentUser()->getTransportManager() === $tma->getTransportManager(),
                'lgvAcquiredRightsReferenceNumber' => $lgvAcquiredRightsReferenceNumber,
            ]
        );
    }
}
