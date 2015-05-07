<?php

/**
 * Licence Overview Helper Service
 */
namespace Olcs\Service\Helper;

use Common\Service\Helper\AbstractHelperService;
use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Entity\LicenceEntityService;

/**
 * Licence Overview Helper Service
 */
class LicenceOverviewHelperService extends AbstractHelperService
{
    /**
     * Collate all the read-only data for the view
     *
     * @param array $licence licence data
     * @return array view data
     */
    public function getViewData($licence)
    {
        $isPsv = $licence['goodsOrPsv']['id'] == LicenceEntityService::LICENCE_CATEGORY_PSV;

        $isSpecialRestricted = $licence['licenceType']['id'] == LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED;

        $previousEntityData = $this->getPreviousEntityDataForLicence($licence);

        $viewData = [
            'operatorName'              => $licence['organisation']['name'],
            'operatorId'                => $licence['organisation']['id'], // used for URL generation
            'numberOfLicences'          => count($licence['organisation']['licences']),
            'tradingName'               => $this->getTradingNameFromLicence($licence),
            'currentApplications'       => $this->getCurrentApplications($licence),
            'licenceNumber'             => $licence['licNo'],
            'licenceStartDate'          => $licence['inForceDate'],
            'licenceType'               => $licence['licenceType']['id'] ?: '',
            'licenceStatus'             => $licence['status']['id'],
            'licenceGracePeriods'       => $this->getLicenceGracePeriods($licence),
            'surrenderedDate'           => $this->getSurrenderedDate($licence),
            'numberOfVehicles'          => $isSpecialRestricted ? null : count($licence['licenceVehicles']),
            'totalVehicleAuthorisation' => $isSpecialRestricted ? null : $licence['totAuthVehicles'],
            'numberOfOperatingCentres'  => $isSpecialRestricted ? null : count($licence['operatingCentres']),
            'totalTrailerAuthorisation' => $isPsv ? null : $licence['totAuthTrailers'],
            'numberOfIssuedDiscs'       => $isPsv && !$isSpecialRestricted ? count($licence['psvDiscs']) : null,
            'numberOfCommunityLicences' => $this->getNumberOfCommunityLicences($licence),
            'openCases'                 => $this->getOpenCases($licence['id']),
            'currentReviewComplaints'   => $this->getReviewComplaintsCount($licence),
            'previousOperatorName'      => $previousEntityData['operator'],
            'previousLicenceNumber'     => $previousEntityData['licence'],
            'isPsv'                     => $isPsv,
            'receivesMailElectronically' => $licence['organisation']['allowEmail'],

            // out of scope for OLCS-5209
            'registeredForSelfService'   => null,
        ];

        return $viewData;
    }

    /**
     * Helper method to get the first trading name from licence data.
     * (Sorts trading names by createdOn date then alphabetically)
     *
     * @param array $licence licence data
     * @return string
     */
    public function getTradingNameFromLicence($licence)
    {
        if (empty($licence['organisation']['tradingNames'])) {
            return 'None';
        }

        usort(
            $licence['organisation']['tradingNames'],
            function ($a, $b) {
                if ($a['createdOn'] == $b['createdOn']) {
                    // This *should* be an extreme edge case but there is a bug
                    // in Business Details causing trading names to have the
                    // same createdOn date. Sort alphabetically to avoid
                    // 'random' behaviour.
                    return strcasecmp($a['name'], $b['name']);
                }
                return strtotime($a['createdOn']) < strtotime($b['createdOn']) ? -1 : 1;
            }
        );

        return array_shift($licence['organisation']['tradingNames'])['name'];
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
        $applications = $this->getServiceLocator()->get('Entity\Organisation')->getAllApplicationsByStatus(
            $licence['organisation']['id'],
            [
                ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION,
                ApplicationEntityService::APPLICATION_STATUS_GRANTED,
            ]
        );

        return count($applications);
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
        $type = $licence['licenceType']['id'];
        $goodsOrPsv = $licence['goodsOrPsv']['id'];

        if ($type == LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL
            || ($goodsOrPsv == LicenceEntityService::LICENCE_CATEGORY_PSV
                && $type == LicenceEntityService::LICENCE_TYPE_RESTRICTED)
        ) {
            return (int) $licence['totCommunityLicences'];
        }

        return null;
    }

    /**
     * @param int $licenceId
     * @return string (count may be suffixed with '(PI)')
     */
    public function getOpenCases($licenceId)
    {
        $cases = $this->getServiceLocator()->get('Entity\Cases')
            ->getOpenForLicence($licenceId);

        $openCases = (string) count($cases);

        foreach ($cases as $c) {
            if (!empty($c['publicInquirys'])) {
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
        $previousData = array(
            'operator' => null,
            'licence' => null
        );

        if (empty($licence['changeOfEntitys'])) {
            return $previousData;
        }

        $changeOfEntity = array_shift($licence['changeOfEntitys']);

        $previousData['operator'] = $changeOfEntity['oldOrganisationName'];
        $previousData['licence'] = $changeOfEntity['oldLicenceNo'];

        return $previousData;
    }

    /**
     * @param $licence
     * @return int
     */
    public function getReviewComplaintsCount($licence)
    {
        $caseEntityService = $this->getServiceLocator()->get('Entity\Cases');
        $licenceCases = $caseEntityService->getOpenComplaintsForLicence($licence['id']);

        $count = 0;
        foreach ($licenceCases as $licenceCase) {
            $count = $count + count($licenceCase['complaints']);
        }

        return $count;
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
            LicenceEntityService::LICENCE_STATUS_SURRENDERED,
            LicenceEntityService::LICENCE_STATUS_TERMINATED
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
        $url = $this->getServiceLocator()
            ->get('Helper\Url')
            ->fromRoute(
                'licence/grace-periods',
                array(
                    'licence' => $licence['id'],
                )
            );

        $gracePeriodEntityService = $this->getServiceLocator()->get('Entity\GracePeriod');
        $gracePeriods = $gracePeriodEntityService->getGracePeriodsForLicence($licence['id']);

        if ($gracePeriods['Count'] === 0) {
            $status = 'None';
        } else {
            $status = 'Inactive';

            $gracePeriodHelperService = $this->getServiceLocator()->get('Helper\LicenceGracePeriod');

            foreach ($gracePeriods['Results'] as $gracePeriod) {
                if ($gracePeriodHelperService->isActive($gracePeriod)) {
                    $status = 'Active';
                    break;
                }
            }
        }

        return sprintf('%s (<a href="%s">manage</a>)', $status, $url);
    }
}
