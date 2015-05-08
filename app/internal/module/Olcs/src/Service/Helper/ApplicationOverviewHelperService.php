<?php

/**
 * Application Overview Helper Service
 */
namespace Olcs\Service\Helper;

use Common\Service\Helper\AbstractHelperService;
use Common\Service\Entity\LicenceEntityService as Licence;

/**
 * Application Overview Helper Service
 */
class ApplicationOverviewHelperService extends AbstractHelperService
{
    /**
     * @param array $application application overview data
     * @param array $licence licence overview data
     * @param string $lva 'application'|'variation' used for URL generation
     * @return array view data
     */
    public function getViewData($application, $licence, $lva)
    {
        $licenceOverviewHelper = $this->getServiceLocator()->get('Helper\LicenceOverview');

        $isPsv = $application['goodsOrPsv']['id'] == Licence::LICENCE_CATEGORY_PSV;
        $isSpecialRestricted = $application['licenceType']['id'] == Licence::LICENCE_TYPE_SPECIAL_RESTRICTED;

        $viewData = [
            'operatorName'              => $licence['organisation']['name'],
            'operatorId'                => $licence['organisation']['id'], // used for URL generation
            'numberOfLicences'          => count($licence['organisation']['licences']),
            'tradingName'               => $licenceOverviewHelper->getTradingNameFromLicence($licence),
            'currentApplications'       => $licenceOverviewHelper->getCurrentApplications($licence),
            'applicationCreated'        => $application['createdOn'],
            'oppositionCount'           => $this->getOppositionCount($application['id']),
            'licenceStatus'             => $licence['status']['id'],
            'interimStatus'             => $isPsv ? null :$this->getInterimStatus($application['id'], $lva),
            'outstandingFees'           => $this->getOutstandingFeeCount($application['id']),
            'licenceStartDate'          => $licence['inForceDate'],
            'licenceGracePeriods'       => $this->getLicenceGracePeriods($licence),
            'continuationDate'          => $licence['expiryDate'],
            'numberOfVehicles'          => $isSpecialRestricted ? null : count($licence['licenceVehicles']),
            'totalVehicleAuthorisation' => $this->getTotalVehicleAuthorisation($application, $licence),
            'numberOfOperatingCentres'  => $isSpecialRestricted ? null : count($licence['operatingCentres']),
            'totalTrailerAuthorisation' => $this->getTotalTrailerAuthorisation($application, $licence),
            'numberOfIssuedDiscs'       => $isPsv && !$isSpecialRestricted ? count($licence['psvDiscs']) : null,
            'numberOfCommunityLicences' => $licenceOverviewHelper->getNumberOfCommunityLicences($licence),
            'openCases'                 => $licenceOverviewHelper->getOpenCases($licence['id']),

            'changeOfEntity'            => (
                (boolean)$application['isVariation'] ?
                null :
                $this->getChangeOfEntity($application['id'], $licence['id'])
            ),

            'receivesMailElectronically' => (
                isset($application['organisation']) ?
                $application['organisation']['allowEmail'] :
                $licence['organisation']['allowEmail']
            ),

            'currentReviewComplaints'   => null, // pending OLCS-7581
            'previousOperatorName'      => null, // pending OLCS-8383
            'previousLicenceNumber'     => null, // pending OLCS-8383

            // out of scope for OLCS-6831
            'outOfOpposition'            => null,
            'outOfRepresentation'        => null,
            'registeredForSelfService'   => null,
        ];

        return $viewData;
    }

    /**
     * @param int $id application id
     * @param string $lva 'application'|'variation' used for URL generation
     * @return string
     */
    public function getInterimStatus($id, $lva)
    {
        $applicationData = $this->getServiceLocator()->get('Entity\Application')
            ->getDataForInterim($id);

        $url = $this->getServiceLocator()->get('Helper\Url')
            ->fromRoute('lva-'.$lva.'/interim', [], [], true);

        if (
            isset($applicationData['interimStatus']['id'])
            && !empty($applicationData['interimStatus']['id'])
        ) {
            $interimStatus = sprintf(
                '%s (<a href="%s">Interim details</a>)',
                $applicationData['interimStatus']['description'],
                $url
            );
        } else {
            $interimStatus = sprintf('None (<a href="%s">add interim</a>)', $url);
        }

        return $interimStatus;
    }


    /**
     * The the change of entity status.
     *
     * @param int $applicationId The current application id.
     * @param int $licenceId The current licence id.
     *
     * @return string A string representing the change of entity status.
     */
    public function getChangeOfEntity($applicationId, $licenceId)
    {
        $args = array(
            'application' => $applicationId
        );

        $changeOfEntity = $this->getServiceLocator()
            ->get('Entity\ChangeOfEntity')
            ->getForLicence($licenceId);

        if ($changeOfEntity['Count'] > 0) {
            $text = array(
                'Yes', 'update details'
            );

            $args['changeId'] = $changeOfEntity['Results'][0]['id'];
        } else {
            $text = array(
                'No', 'add details'
            );
        }

        $url = $this->getServiceLocator()->get('Helper\Url')
            ->fromRoute('lva-application/change-of-entity', $args);
        $value = sprintf('%s (<a class="js-modal-ajax" href="' . $url . '">%s</a>)', $text[0], $text[1]);

        return $value;
    }

    /**
     * @param int $applicationId
     * @return int count
     */
    public function getOutstandingFeeCount($applicationId)
    {
        $fees = $this->getServiceLocator()->get('Entity\Fee')
            ->getOutstandingFeesForApplication($applicationId);

        return count($fees);
    }

    /**
     * @param int @applicationId
     * @return int count
     */
    public function getOppositionCount($applicationId)
    {
        $oppositions = $this->getServiceLocator()->get('Entity\Opposition')
            ->getForApplication($applicationId);

        return count($oppositions);
    }

    /**
     * @param array $application application overview data
     * @param array $licence licence overview data
     * @return string
     */
    public function getTotalVehicleAuthorisation($application, $licence)
    {
        if ($application['licenceType']['id'] == Licence::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            return null;
        }

        $str = (string) (int) $licence['totAuthVehicles'];

        if ($application['totAuthVehicles'] != $licence['totAuthVehicles']) {
            $str .= ' (' . (string) (int) $application['totAuthVehicles'] . ')';
        }

        return $str;
    }

    /**
     * @param array $application application overview data
     * @param array $licence licence overview data
     * @return string
     */
    public function getTotalTrailerAuthorisation($application, $licence)
    {
        if ($application['goodsOrPsv']['id'] == Licence::LICENCE_CATEGORY_PSV) {
            return null;
        }

        $str = (string) (int) $licence['totAuthTrailers'];

        if ($application['totAuthTrailers'] != $licence['totAuthTrailers']) {
            $str .= ' (' . (string) (int) $application['totAuthTrailers'] . ')';
        }

        return $str;
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
