<?php

/**
 * Licence Overview Helper Service
 */

namespace Olcs\Service\Helper;

use Common\Service\Helper\UrlHelperService;
use Common\RefData;

/**
 * Licence Overview Helper Service
 */
class LicenceOverviewHelperService
{
    /** @var UrlHelperService */
    protected $urlHelperService;

    /**
     * Create service instance
     *
     * @param UrlHelperService $urlHelperService
     *
     * @return LicenceOverviewHelperService
     */
    public function __construct(
        UrlHelperService $urlHelperService
    ) {
        $this->urlHelperService = $urlHelperService;
    }

    /**
     * Collate all the read-only data for the view
     *
     * @param array $licence licence data
     * @return array view data
     *
     * @SuppressWarnings("PMD.NPathComplexity")
     */
    public function getViewData($licence)
    {
        $isPsv = $licence['goodsOrPsv']['id'] == RefData::LICENCE_CATEGORY_PSV;

        $isSpecialRestricted = $licence['licenceType']['id'] == RefData::LICENCE_TYPE_SPECIAL_RESTRICTED;

        $previousEntityData = $this->getPreviousEntityDataForLicence($licence);

        $viewData = [
            'operatorName'              => $licence['organisation']['name'],
            'operatorId'                => $licence['organisation']['id'], // used for URL generation
            'numberOfLicences'          => $licence['organisationLicenceCount'],
            'tradingName'               => $licence['tradingName'],
            'currentApplications'       => $this->getCurrentApplications($licence),
            'licenceNumber'             => $licence['licNo'],
            'licenceStartDate'          => $licence['inForceDate'],
            'licenceType'               => $licence['licenceType']['id'] ?: '',
            'licenceStatus'             => $licence['status'],
            'licenceGracePeriods'       => $this->getLicenceGracePeriods($licence),
            'surrenderedDate'           => $this->getSurrenderedDate($licence),
            'numberOfVehicles'          => $isSpecialRestricted ? null : $licence['numberOfVehicles'],
            'numberOfOperatingCentres'  => $isSpecialRestricted ? null : count($licence['operatingCentres']),
            'numberOfIssuedDiscs'       => $isPsv && !$isSpecialRestricted ? count($licence['psvDiscs']) : null,
            'numberOfCommunityLicences' => $this->getNumberOfCommunityLicences($licence),
            'openCases'                 => $this->getOpenCases($licence),
            'currentReviewComplaints'   => $licence['complaintsCount'],
            'previousOperatorName'      => $previousEntityData['operator'],
            'previousLicenceNumber'     => $previousEntityData['licence'],
            'isPsv'                     => $isPsv,
            'receivesMailElectronically' => $licence['organisation']['allowEmail'],
            'numberOfBusRegistrations'  => $licence['busCount'],
            // out of scope for OLCS-5209
            'registeredForSelfService'   => $this->hasAdminUsers($licence) ? 'Yes' : 'No',
        ];

        $viewData = array_merge(
            $viewData,
            $this->getAuthorisationViewData($licence)
        );

        return $viewData;
    }

    /**
     * Gets authorisation view data appropriate to the specified licence data
     *
     * @param array $licence
     *
     * @return array
     */
    private function getAuthorisationViewData(array $licence)
    {
        $templateKeyLookup = [
            'totAuthVehicles' => 'totalVehicleAuthorisation',
            'totAuthHgvVehicles' => 'totalHgvAuthorisation',
            'totAuthLgvVehicles' => 'totalLgvAuthorisation',
            'totAuthTrailers' => 'totalTrailerAuthorisation',
        ];

        $viewData = [];
        foreach ($licence['applicableAuthProperties'] as $entityKey) {
            $templateKey = $templateKeyLookup[$entityKey];
            $viewData[$templateKey] = $licence[$entityKey];
        }

        return $viewData;
    }

    /**
     * Helper method to get number of current applications for the organisation
     * from licence data
     *
     * @param array $licence
     * @return int
     */
    public function getCurrentApplications($licence)
    {
        $count = is_array($licence['currentApplications'])
            ? count($licence['currentApplications'])
            : 0;

        if ($count < 1) {
            return $count;
        }

        $url = $this->urlHelperService->fromRoute(
            'operator/applications',
            ['organisation' => (int) $licence['organisation']['id']]
        );

        return sprintf('%s (<a class="govuk-link" href="%s">view</a>)', $count, $url);
    }

    /**
     * Helper method to get number of community licences from licence data
     * (Standard International and PSV Restricted only, otherwise null)
     *
     * @param array $licence
     * @return int|null
     */
    public function getNumberOfCommunityLicences($licence)
    {
        $type = $licence['licenceType']['id'] ?? null;
        $goodsOrPsv = $licence['goodsOrPsv']['id'] ?? null;

        if (
            $type == RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
            || ($goodsOrPsv == RefData::LICENCE_CATEGORY_PSV
                && $type == RefData::LICENCE_TYPE_RESTRICTED)
        ) {
            return (int) $licence['totCommunityLicences'];
        }

        return null;
    }

    /**
     * @param int $licenceId
     * @return string (count may be suffixed with '(PI)')
     */
    public function getOpenCases($licence)
    {
        $cases = $licence['openCases'];

        if (empty($cases)) {
            return 0;
        }

        $openCases = (string) count($cases);

        foreach ($cases as $c) {
            if (!empty($c['publicInquiry'])) {
                $openCases .= ' (PI)';
                break;
            }
        }

        return $openCases;
    }

    /**
     * Get previous licence entity data.
     *
     * @param $licence The licence data.
     *
     * @return array The formatted data.
     */
    public function getPreviousEntityDataForLicence($licence)
    {
        $previousData = [
            'operator' => null,
            'licence' => null
        ];

        if (empty($licence['changeOfEntitys'])) {
            return $previousData;
        }

        $changeOfEntity = array_shift($licence['changeOfEntitys']);

        $previousData['operator'] = $changeOfEntity['oldOrganisationName'];
        $previousData['licence'] = $changeOfEntity['oldLicenceNo'];

        return $previousData;
    }

    /**
     * Helper method to get the surrendered/terminated date (if any)
     * from licence data
     *
     * @param array $licence
     * @return string|null
     */
    public function getSurrenderedDate($licence)
    {
        $surrenderedDate = null;

        $statuses = [
            RefData::LICENCE_STATUS_SURRENDERED,
            RefData::LICENCE_STATUS_TERMINATED
        ];

        if (in_array($licence['status']['id'], $statuses)) {
            $surrenderedDate = $licence['surrenderedDate'];
        }

        return $surrenderedDate;
    }

    /**
     * Determine what to display for the user based on rules around licence grace periods.
     *
     * @param $licence The licence data.
     *
     * @return string
     */
    public function getLicenceGracePeriods($licence)
    {
        if (empty($licence['gracePeriods'])) {
            $status = 'None';
        } else {
            $status = 'Inactive';
            foreach ($licence['gracePeriods'] as $gracePeriod) {
                if ($gracePeriod['isActive'] == true) {
                    $status = 'Active';
                    break;
                }
            }
        }

        $url = $this->urlHelperService->fromRoute('licence/grace-periods', ['licence' => $licence['id']]);

        return sprintf('%s (<a class="govuk-link" href="%s">manage</a>)', $status, $url);
    }

    public function hasAdminUsers($licence)
    {
        if (isset($licence['organisation']['organisationUsers'])) {
            foreach ($licence['organisation']['organisationUsers'] as $user) {
                if ($user['isAdministrator'] === 'Y') {
                    return true;
                }
            }
        }
        return false;
    }
}
