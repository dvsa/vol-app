<?php

/**
 * Application Overview Helper Service
 */

namespace Olcs\Service\Helper;

use Common\Service\Helper\UrlHelperService;
use Common\RefData;

/**
 * Application Overview Helper Service
 */
class ApplicationOverviewHelperService
{
    /** @var LicenceOverviewHelperService */
    protected $licenceOverviewHelperService;

    /** @var UrlHelperService */
    protected $urlHelperService;

    /**
     * Create service instance
     *
     *
     * @return ApplicationOverviewHelperService
     */
    public function __construct(
        LicenceOverviewHelperService $licenceOverviewHelperService,
        UrlHelperService $urlHelperService
    ) {
        $this->licenceOverviewHelperService = $licenceOverviewHelperService;
        $this->urlHelperService = $urlHelperService;
    }

    /**
     * Gets application view data
     *
     * @param array  $application application overview data
     * @param string $lva         'application'|'variation' used for URL generation
     *
     * @return array view data
     */
    public function getViewData($application, $lva)
    {
        $licence = $application['licence'];

        $viewData = [
            'operatorName'              => $licence['organisation']['name'],
            'operatorId'                => $licence['organisation']['id'], // used for URL generation
            'numberOfLicences'          => $licence['organisationLicenceCount'],
            'tradingName'               => $licence['tradingName'],
            'currentApplications'       => $this->licenceOverviewHelperService->getCurrentApplications($licence),
            'applicationCreated'        => $application['createdOn'],
            'oppositionCount'           => $application['oppositionCount'],
            'licenceStatus'             => $licence['status'],
            'licenceType'               => $licence['licenceType']['id'] ?? null,
            'appLicenceType'            => $application['licenceType']['id'],
            'interimStatus'             => $this->getInterimStatus($application, $lva),
            'isPsv'                     => $application['goodsOrPsv']['id'] == RefData::LICENCE_CATEGORY_PSV,
            'outstandingFees'           => $application['feeCount'],
            'licenceStartDate'          => $licence['inForceDate'],
            'licenceGracePeriods'       => $this->licenceOverviewHelperService->getLicenceGracePeriods($licence),
            'continuationDate'          => $licence['expiryDate'],
            'numberOfVehicles'          => $this->getNumberOfVehicles($application, $licence),
            'numberOfOperatingCentres'  => $this->getNumberOfOperatingCentres($application, $licence),
            'numberOfIssuedDiscs'       => $this->getNumberOfIssuedDiscs($application, $licence),
            'numberOfCommunityLicences' => $this->licenceOverviewHelperService->getNumberOfCommunityLicences($licence),
            'openCases'                 => $this->licenceOverviewHelperService->getOpenCases($licence),

            'changeOfEntity'            => (
                (bool)$application['isVariation'] ?
                null :
                $this->getChangeOfEntity($application)
            ),

            'receivesMailElectronically' => (
                isset($application['organisation']) ?
                $application['organisation']['allowEmail'] :
                $licence['organisation']['allowEmail']
            ),

            'currentReviewComplaints'   => null, // pending OLCS-7581
            'previousOperatorName'      => null, // pending OLCS-8383
            'previousLicenceNumber'     => null, // pending OLCS-8383

            'outOfOpposition'            => $application['outOfOppositionDate'],
            'outOfRepresentation'        => $application['outOfRepresentationDate'],
            'registeredForSelfService'   => $this->licenceOverviewHelperService->hasAdminUsers($application['licence']) ? 'Yes' : 'No',
        ];

        $viewData = array_merge(
            $viewData,
            $this->getAuthorisationViewData($application, $licence)
        );

        return $viewData;
    }

    /**
     * Gets authorisation view data appropriate to the specified application and licence data
     *
     *
     * @return array
     */
    private function getAuthorisationViewData(array $application, array $licence)
    {
        $templateKeyLookup = [
            'totAuthVehicles' => 'totalVehicleAuthorisation',
            'totAuthHgvVehicles' => 'totalHgvAuthorisation',
            'totAuthLgvVehicles' => 'totalLgvAuthorisation',
            'totAuthTrailers' => 'totalTrailerAuthorisation',
        ];

        $viewData = [];
        foreach ($application['applicableAuthProperties'] as $entityKey) {
            $templateKey = $templateKeyLookup[$entityKey];
            $viewData[$templateKey] = $this->getVehicleAuthorisation($application, $licence, $entityKey);
        }

        return $viewData;
    }

    /**
     * Gets interim status string
     *
     * @param array  $application application overview data
     * @param string $lva         'application'|'variation' used for URL generation
     *
     * @return string|null
     */
    public function getInterimStatus($application, $lva)
    {
        if (
            isset($application['goodsOrPsv']['id'])
            && $application['goodsOrPsv']['id'] == RefData::LICENCE_CATEGORY_PSV
        ) {
            return null;
        }

        $url = $this->urlHelperService->fromRoute('lva-' . $lva . '/interim', [], [], true);

        if (isset($application['interimStatus']['id']) && !empty($application['interimStatus']['id'])) {
            $interimStatus = sprintf(
                '%s (<a class="govuk-link" href="%s">Interim details</a>)',
                $application['interimStatus']['description'],
                $url
            );
        } else {
            $interimStatus = sprintf('None (<a class="govuk-link" href="%s">add interim</a>)', $url);
        }

        return $interimStatus;
    }


    /**
     * Gets the change of entity status.
     *
     * @param array $application application data
     *
     * @return string A string representing the change of entity status.
     */
    public function getChangeOfEntity($application)
    {
        $args = [
            'application' => $application['id'],
        ];

        $changeOfEntity = $application['licence']['changeOfEntitys'];

        if (!empty($changeOfEntity)) {
            $text = [
                'Yes', 'update details'
            ];

            $args['changeId'] = $changeOfEntity[0]['id'];
        } else {
            $text = [
                'No', 'add details'
            ];
        }

        $url = $this->urlHelperService->fromRoute('lva-application/change-of-entity', $args);
        $value = sprintf('%s (<a class="govuk-link js-modal-ajax" href="' . $url . '">%s</a>)', $text[0], $text[1]);

        return $value;
    }

    /**
     * Gets vehicle authorisation
     *
     * @param array  $application application overview data
     * @param array  $licence     licence overview data
     * @param string $key         key to be used from application and licence data
     *
     * @return string|null
     */
    private function getVehicleAuthorisation($application, $licence, $key)
    {
        $str = (string) (int) $licence[$key];

        if ($application[$key] != $licence[$key]) {
            $str .= ' (' . (string) (int) $application[$key] . ')';
        }

        return $str;
    }

    /**
     * Gets number of operating centres
     *
     * @param array $application application overview data
     * @param array $licence     licence overview data
     *
     * @return string|null
     */
    protected function getNumberOfOperatingCentres($application, $licence)
    {
        if ($application['licenceType']['id'] == RefData::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            return null;
        }
        return sprintf(
            '%d (%d)',
            count($licence['operatingCentres']),
            count($licence['operatingCentres']) + $application['operatingCentresNetDelta']
        );
    }

    /**
     * Gets number of vehicles
     *
     * @param array $application application overview data
     * @param array $licence     licence overview data
     *
     * @return string|null
     */
    protected function getNumberOfVehicles($application, $licence)
    {
        if ($application['licenceType']['id'] == RefData::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            return null;
        }
        return sprintf(
            '%d (%d)',
            $licence['numberOfVehicles'],
            $licence['numberOfVehicles'] + count($application['licenceVehicles'])
        );
    }

    /**
     * Gets number of issued discs
     *
     * @param array $application application overview data
     * @param array $licence     licence overview data
     *
     * @return string|null
     */
    protected function getNumberOfIssuedDiscs($application, $licence)
    {
        $isPsv = $application['goodsOrPsv']['id'] == RefData::LICENCE_CATEGORY_PSV;
        $isSpecialRestricted = $application['licenceType']['id'] == RefData::LICENCE_TYPE_SPECIAL_RESTRICTED;

        return $isPsv && !$isSpecialRestricted ? count($licence['psvDiscs']) : null;
    }
}
