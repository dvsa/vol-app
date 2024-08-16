<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Search;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * @see \Dvsa\OlcsTest\Api\Domain\QueryHandler\Search\LicenceTest
 */
class Licence extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['Organisation'];

    public function handleQuery(QueryInterface $query)
    {
        /** @var LicenceEntity $licence */
        $licence = $this->getRepo()->fetchUsingId($query);

        $this->getAuditLogger()->logAccessToEntity($licence);

        $applications = $licence->getApplications();

        $applicationsArray = [];

        /** @var Application $application */
        foreach ($applications as $application) {
            $application->setPublicationNo(
                $application->determinePublicationNo()
            );
            $application->setPublishedDate(
                $application->determinePublishedDate()
            );

            $application->setOooDate(
                $application->getOutOfOppositionDateAsString()
            );
            $application->setOorDate(
                $application->getOutOfRepresentationDateAsString()
            );
            $application->setIsOpposed(
                $application->hasOpposition()
            );

            $applicationsArray[$application->getId()] = $application;
        }

        $result = $this->result(
            $licence,
            [],
            [
                'tradingNames' => $licence->getAllTradingNames(),
                'licenceContactDetails' => [
                    'phoneContacts' => !empty($licence->getCorrespondenceCd()) ? $this->resultList(
                        $licence->getCorrespondenceCd()->getPhoneContacts(),
                        ['phoneContactType']
                    ) : null,
                    'address' => !empty($licence->getCorrespondenceCd()) ?
                        $this->result($licence->getCorrespondenceCd()->getAddress())->serialize() : null,
                    'emailAddress' => !empty($licence->getCorrespondenceCd()) ?
                        $licence->getCorrespondenceCd()->getEmailAddress() : null,
                ],
                'totalAuthVehicles' => $licence->getTotAuthVehicles(),
                'totalAuthTrailers' => $licence->getTotAuthTrailers(),
                'totalVehiclesInPossession' => $licence->getActiveVehiclesCount(),
                'totalPiRecords' => $licence->getPiRecordCount(),
                'activeCommunityLicences' => count($licence->getActiveCommunityLicences()),
                'trafficArea' => $this->result(
                    $licence->getTrafficArea()
                )->serialize(),
                'companySubsidiaries' => $this->resultList(
                    $licence->getCompanySubsidiaries()
                ),
                'organisation' => $this->result(
                    $licence->getOrganisation(),
                    [
                        'type',
                        'contactDetails' => [
                            'address' => [
                                'countryCode'
                            ],
                            'phoneContacts' => [
                                'phoneContactType'
                            ]
                        ],
                        'leadTcArea' => [
                            'contactDetails' => [
                                'person'
                            ]
                        ]
                    ]
                )->serialize(),
                'correspondenceAddress' => !empty($licence->getCorrespondenceCd()) ? $this->result(
                    $licence->getCorrespondenceCd(),
                    [
                        'person',
                        'phoneContacts',
                        'address' => [
                            'countryCode'
                        ]
                    ]
                )->serialize() : null,
                'people' => $this->resultList(
                    $licence->getOrganisation()->getOrganisationPersons(),
                    [
                        'person'
                    ]
                ),
                'transportManagers' => $this->resultList(
                    $licence->getTmLicences(),
                    [
                        'transportManager' => [
                            'homeCd' => [
                                'person'
                            ]
                        ]

                    ]
                ),
                'operatingCentres' => $this->resultList(
                    $licence->getOperatingCentres(),
                    [
                        'operatingCentre' => [
                            'address' => [
                                'countryCode'
                            ]
                        ]
                    ]
                ),
                'authorisations' => $licence->getAuthorisations(),
                'vehicles' => $this->resultList(
                    $licence->getActiveVehicles(true, true),
                    [
                        'vehicle',
                        'interimApplication'
                    ]
                ),
                'applications' => $this->resultList(
                    $applicationsArray,
                    ['interimStatus']
                ),
                'conditionUndertakings' => $this->resultList(
                    $licence->getConditionUndertakings(),
                    [
                        'conditionType'
                    ]
                ),
                'otherLicences' => $this->resultList($licence->getOtherActiveLicences()),
                'disqualificationStatus' => $licence->getOrganisation()->getDisqualificationStatus(),
            ]
        );

        return $result;
    }
}
