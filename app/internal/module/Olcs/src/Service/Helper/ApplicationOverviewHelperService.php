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
            'continuationDate'          => $licence['expiryDate'],
            'numberOfVehicles'          => $isSpecialRestricted ? null : count($licence['licenceVehicles']),
            'totalVehicleAuthorisation' => $this->getTotalVehicleAuthorisation($application, $licence),
            'numberOfOperatingCentres'  => $isSpecialRestricted ? null : count($licence['operatingCentres']),
            'totalTrailerAuthorisation' => $this->getTotalTrailerAuthorisation($application, $licence),
            'numberOfIssuedDiscs'       => $isPsv && !$isSpecialRestricted ? count($licence['psvDiscs']) : null,
            'numberOfCommunityLicences' => $licenceOverviewHelper->getNumberOfCommunityLicences($licence),
            'openCases'                 => $licenceOverviewHelper->getOpenCases($licence['id']),

            'currentReviewComplaints'   => null, // @todo pending OLCS-7581
            'previousOperatorName'      => null, // @todo pending OLCS-8383
            'previousLicenceNumber'     => null, // @todo pending OLCS-8383

            // out of scope for OLCS-6831
            'outOfOpposition'            => null,
            'outOfRepresentation'        => null,
            'changeOfEntity'             => null,
            'receivesMailElectronically' => null,
            'registeredForSelfService'   => null,
        ];

        return $viewData;
    }

    protected function getInterimStatus($id, $lva)
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

    protected function getOutstandingFeeCount($applicationId)
    {
        $fees = $this->getServiceLocator()->get('Entity\Fee')
            ->getOutstandingFeesForApplication($applicationId);

        return count($fees);
    }

    protected function getOppositionCount($applicationId)
    {
        $oppositions = $this->getServiceLocator()->get('Entity\Opposition')
            ->getForApplication($applicationId);

        return count($oppositions);
    }

    protected function getTotalVehicleAuthorisation($application, $licence)
    {
        if ($application['licenceType']['id'] == Licence::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            return null;
        }

        $str = (string) (int) $licence['totAuthVehicles'];

        if ($application['totAuthVehicles'] != $licence['totAuthVehicles']) {
            $str .= ' (' . $application['totAuthVehicles'] . ')';
        }

        return $str;
    }

    protected function getTotalTrailerAuthorisation($application, $licence)
    {
        if ($application['goodsOrPsv']['id'] == Licence::LICENCE_CATEGORY_PSV) {
            return null;
        }

        $str = (string) (int) $licence['totAuthTrailers'];

        if ($application['totAuthTrailers'] != $licence['totAuthTrailers']) {
            $str .= ' (' . $application['totAuthTrailers'] . ')';
        }

        return $str;
    }
}
