<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;

/**
 * Class CaseSummary
 * @package Dvsa\Olcs\Api\Service\Submission\Section\CaseSummary
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
final class CaseSummary extends AbstractSection
{
    public const array AUTH_PROPERTY_TO_TEMPLATE_ENTRY_MAPPINGS = [
        'totAuthVehicles' => [
            [
                'templateKey' => 'totAuthorisedVehicles',
                'accessMethod' => 'getTotAuthVehicles'
            ]
        ],
        'totAuthHgvVehicles' => [
            [
                'templateKey' => 'totAuthorisedHgvVehicles',
                'accessMethod' => 'getTotAuthHgvVehicles'
            ]
        ],
        'totAuthLgvVehicles' => [
            [
                'templateKey' => 'totAuthorisedLgvVehicles',
                'accessMethod' => 'getTotAuthLgvVehicles'
            ]
        ],
        'totAuthTrailers' => [
            [
                'templateKey' => 'totAuthorisedTrailers',
                'accessMethod' => 'getTotAuthTrailers'
            ],
            [
                'templateKey' => 'trailersInPossession',
                'accessMethod' => 'getTotAuthTrailers'
            ]
        ],
    ];

    /**
     * Generate CaseSummary Submission Section
     *
     * @param CasesEntity $case Case relating to the submission
     *
     * @return array Data array containing information for the submission section
     */
    public function generateSection(CasesEntity $case)
    {
        $licence = $case->getLicence();
        $organisation = !empty($licence) ? $licence->getOrganisation() : '';
        $application = $case->getApplication();

        // case data
        $data = [
            'id' => $case->getId(),
            'caseType' => $case->getCaseType()->getDescription(),
            'ecmsNo' => $case->getEcmsNo(),
        ];

        // organisation data
        $data += $this->extractOrganisationData($organisation);

        // licence data
        $data += $this->extractLicenceData($licence, $application);

        // application data
        $data['serviceStandardDate'] = !empty($application) ?
            $this->formatDate($application->getTargetCompletionDate()) : '';

        if (!empty($application)) {
            $data['licenceType'] = !empty($application->getLicenceType())
                ? $application->getLicenceType()->getDescription()
                : '';

            $data['licenceStartDate'] = !empty($data['licenceStartDate']) ? $data['licenceStartDate'] : 'N/A';

            $data['goodsOrPsv'] = !empty($application->getGoodsOrPsv())
                ? $application->getGoodsOrPsv()->getDescription()
                : '';
        }

        return ['data' => ['overview' => $data]];
    }

    /**
     * Extract licence/application data required for submission section from the licence
     *
     * @param null|Licence $licence Licence or null
     * @param null|Application $application Application or null
     *
     * @return array
     */
    private function extractLicenceData($licence, $application)
    {
        $licenceData = [
            'licNo' => '',
            'licenceStartDate' => '',
            'licenceType' => '',
            'goodsOrPsv' => '',
            'licenceStatus' => '',
            'vehiclesInPossession' => '',
        ];

        if ($licence instanceof Licence) {
            $licenceData['licNo'] = $licence->getLicNo();
            $licenceData['licenceStartDate'] = $this->formatDate($licence->getInForceDate());
            $licenceData['licenceType'] = !empty($licence->getLicenceType()) ?
                $licence->getLicenceType()->getDescription() : '';
            $licenceData['goodsOrPsv'] =
                !empty($licence->getGoodsOrPsv()) ? $licence->getGoodsOrPsv()->getDescription() : '';
            $licenceData['licenceStatus'] =
                !empty($licence->getStatus()) ? $licence->getStatus()->getDescription() : '';
            $licenceData['vehiclesInPossession'] = $licence->getActiveVehiclesCount();
        }

        //if we don't have either a licence or application we can exit here
        if (is_null($licence) && is_null($application)) {
            return $licenceData;
        }

        $authPropertiesEntity = $licence;
        if ($application instanceof Application && $application->isNew()) {
            $authPropertiesEntity = $application;
        }

        $applicableAuthProperties = $authPropertiesEntity->getApplicableAuthProperties();

        foreach ($applicableAuthProperties as $propertyName) {
            $templateEntries = self::AUTH_PROPERTY_TO_TEMPLATE_ENTRY_MAPPINGS[$propertyName];
            foreach ($templateEntries as $templateEntry) {
                $templateKey = $templateEntry['templateKey'];
                $accessMethod = $templateEntry['accessMethod'];
                $licenceData[$templateKey] = $licence->$accessMethod();
            }
        }

        return $licenceData;
    }

    /**
     * Extract organisation data required for submission section
     *
     * @param null|Organisation $organisation Organisation entity
     *
     * @return array
     */
    private function extractOrganisationData($organisation = null)
    {
        $organisationData = [];
        $organisationData['organisationName'] = '';
        $organisationData['isMlh'] = '';
        $organisationData['organisationType'] = '';
        $organisationData['businessType'] = '';
        $organisationData['disqualificationStatus'] = '';

        if (!empty($organisation) && ($organisation instanceof Organisation)) {
            $organisationData['organisationName'] = $organisation->getName();
            $organisationData['isMlh'] = $organisation->isMlh();
            $organisationData['organisationType'] = $organisation->getType()->getDescription();
            $organisationData['businessType'] = $organisation->getNatureOfBusiness();
            $organisationData['disqualificationStatus'] = $organisation->getDisqualificationStatus();
        }

        return $organisationData;
    }
}
