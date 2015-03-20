<?php

namespace Olcs\Service\Marker;

use Common\Service\Data\AbstractData;
use Zend\ServiceManager\ServiceLocatorInterface;
use Olcs\Service\Marker\CaseMarkers;
use Common\Service\Entity\LicenceEntityService;

/**
 * Class LicenceMarkers service. Used to contain business logic for generating markers
 * @package Olcs\Service
 */
class LicenceMarkers extends CaseMarkers
{
    /**
     * Generates stay marker content
     *
     * @param $stay
     * @return string
     */
    protected function generateStayMarkerContent($stay)
    {
        $content = "Case %s \nStay ";
        $content .= isset($stay['outcome']['id']) ?
            strtolower($stay['outcome']['description']) .  " pending appeal \n" : " in progress \n";
        $content .= $stay['stayType']['id'] == 'stay_t_ut' ?  ' UT ' : ' TC/TR ';
        $requestDate = new \DateTime($stay['requestDate']);
        $content .= $requestDate->format('d/m/Y');

        return $content;
    }

    /**
     * Generates data associated with the content for the marker. For licences, we require a link to be generated
     *
     * @param array $stay
     * @return array
     */
    protected function generateStayMarkerData()
    {
        $data[] = [
            'type' => 'url',
            'route' => 'case_hearing_appeal',
            'params' => ['case' => $this->getCase()['id']],
            'linkText' => $this->getCase()['id']
        ];

        return $data;
    }

    /**
     * Generates appeal marker content
     * @param array $appeal
     * @return string
     */
    protected function generateAppealMarkerContent($appeal)
    {
        $content = "Case %s \nAppeal in progress \n";
        $appealDate = new \DateTime($appeal['appealDate']);
        $content .= $appealDate->format('d/m/Y');

        return $content;
    }

    /**
     * Generates data associated with the content for the marker. For licences, we require a link to be generated
     *
     * @return array
     */
    protected function generateAppealMarkerData()
    {
        return $this->generateStayMarkerData();
    }

    /**
     * Generate the Status markers
     *
     * @param array $data
     * @return array
     */
    protected function generateStatusMarkers($data)
    {
        $marker = [];
        if (!empty($data['statusData'])) {

            $markerStatuses =  [
                LicenceEntityService::LICENCE_STATUS_SUSPENDED,
                LicenceEntityService::LICENCE_STATUS_REVOKED,
                LicenceEntityService::LICENCE_STATUS_CURTAILED,
            ];
            if (in_array($data['statusData']['id'], $markerStatuses)) {
                array_push(
                    $marker,
                    [
                        'content' => $this->generateStatusMarkerContent($data['statusData']['id']),
                        'data' => $this->generateStatusMarkerData(),
                        'type' => 'danger',
                    ]
                );
            }

        }

        return $marker;
    }

    /**
     * Generates data associated with the content for the marker.
     *
     * @param array $stay
     * @return array
     */
    protected function getStatusMarkerData()
    {
        $data = [
            'statusData' => $this->getLicence()['status'],
            'statusRuleData' => [],
        ];

        return $data;
    }

    /**
     * Generates Status marker content
     *
     * @return string
     */
    protected function generateStatusMarkerContent($statusId)
    {
        switch ($statusId) {
            case LicenceEntityService::LICENCE_STATUS_CURTAILED:
                $content = "Date of curtailment\n"
                    . "from XXX to YYY";
                break;
            case LicenceEntityService::LICENCE_STATUS_REVOKED:
                $content = "Date of revocation\n"
                    . "from XXX";
                break;
            case LicenceEntityService::LICENCE_STATUS_SUSPENDED:
                $content = "Date of suspension\n"
                    . "from XXX to YYY";
                break;
            default:
                $content = '';
                break;
        }

        return $content;
    }

    /**
     * Generates data associated with the content for the marker.
     *
     * @return array
     */
    protected function generateStatusMarkerData()
    {
        $data[] = [
            'type' => 'url',
            'route' => 'lva-licence/overview',
            'params' => ['licence' => $this->getLicence()['id']],
            'linkText' => $this->getLicence()['licNo']
        ];
        return $data;
    }
}
