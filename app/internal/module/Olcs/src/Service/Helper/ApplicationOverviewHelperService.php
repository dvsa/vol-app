<?php

/**
 * Application Overview Helper Service
 */
namespace Olcs\Service\Helper;

use Common\Service\Helper\AbstractHelperService;
use Common\RefData;

/**
 * Application Overview Helper Service
 */
class ApplicationOverviewHelperService extends AbstractHelperService
{
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
        $licenceOverviewHelper = $this->getServiceLocator()->get('Helper\LicenceOverview');

        $licence = $application['licence'];

        $viewData = [
            'operatorName'              => $licence['organisation']['name'],
            'operatorId'                => $licence['organisation']['id'], // used for URL generation
            'numberOfLicences'          => $licence['organisationLicenceCount'],
            'tradingName'               => $licence['tradingName'],
            'currentApplications'       => $licenceOverviewHelper->getCurrentApplications($licence),
            'applicationCreated'        => $application['createdOn'],
            'oppositionCount'           => $application['oppositionCount'],
            'licenceStatus'             => $licence['status'],
            'licenceType'               => $licence['licenceType']['id'],
            'appLicenceType'            => $application['licenceType']['id'],
            'interimStatus'             => $this->getInterimStatus($application, $lva),
            'outstandingFees'           => $application['feeCount'],
            'licenceStartDate'          => $licence['inForceDate'],
            'licenceGracePeriods'       => $licenceOverviewHelper->getLicenceGracePeriods($licence),
            'continuationDate'          => $licence['expiryDate'],
            'numberOfVehicles'          => $this->getNumberOfVehicles($application, $licence),
            'totalVehicleAuthorisation' => $this->getTotalVehicleAuthorisation($application, $licence),
            'numberOfOperatingCentres'  => $this->getNumberOfOperatingCentres($application, $licence),
            'totalTrailerAuthorisation' => $this->getTotalTrailerAuthorisation($application, $licence),
            'numberOfIssuedDiscs'       => $this->getNumberOfIssuedDiscs($application, $licence),
            'numberOfCommunityLicences' => $licenceOverviewHelper->getNumberOfCommunityLicences($licence),
            'openCases'                 => $licenceOverviewHelper->getOpenCases($licence),

            'changeOfEntity'            => (
                (boolean)$application['isVariation'] ?
                null :
                $this->getChangeOfEntity($application, $licence)
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
            'registeredForSelfService'   =>
                $this->getServiceLocator()
                    ->get('Helper\LicenceOverview')
                    ->hasAdminUsers($application['licence']) ? 'Yes' : 'No',
        ];

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
        if ($application['goodsOrPsv']['id'] == RefData::LICENCE_CATEGORY_PSV) {
            return null;
        }

        $url = $this->getServiceLocator()->get('Helper\Url')
            ->fromRoute('lva-'.$lva.'/interim', [], [], true);

        if (
            isset($application['interimStatus']['id'])
            && !empty($application['interimStatus']['id'])
        ) {
            $interimStatus = sprintf(
                '%s (<a href="%s">Interim details</a>)',
                $application['interimStatus']['description'],
                $url
            );
        } else {
            $interimStatus = sprintf('None (<a href="%s">add interim</a>)', $url);
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
        $args = array(
            'application' => $application['id'],
        );

        $changeOfEntity = $application['licence']['changeOfEntitys'];

        if (!empty($changeOfEntity)) {
            $text = array(
                'Yes', 'update details'
            );

            $args['changeId'] = $changeOfEntity[0]['id'];
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
     * Gets total vehicle authorisation
     *
     * @param array $application application overview data
     * @param array $licence     licence overview data
     *
     * @return string|null
     */
    public function getTotalVehicleAuthorisation($application, $licence)
    {
        if ($application['licenceType']['id'] == RefData::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            return null;
        }

        $str = (string) (int) $licence['totAuthVehicles'];

        if ($application['totAuthVehicles'] != $licence['totAuthVehicles']) {
            $str .= ' (' . (string) (int) $application['totAuthVehicles'] . ')';
        }

        return $str;
    }

    /**
     * Gets total trailer authorisation
     *
     * @param array $application application overview data
     * @param array $licence     licence overview data
     *
     * @return string|null
     */
    public function getTotalTrailerAuthorisation($application, $licence)
    {
        if ($application['goodsOrPsv']['id'] == RefData::LICENCE_CATEGORY_PSV) {
            return null;
        }

        $str = (string) (int) $licence['totAuthTrailers'];

        if ($application['totAuthTrailers'] != $licence['totAuthTrailers']) {
            $str .= ' (' . (string) (int) $application['totAuthTrailers'] . ')';
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
