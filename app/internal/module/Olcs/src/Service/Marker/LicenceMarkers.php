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
    const DATE_FORMAT = 'd/m/Y H:i';

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
        $content .= $this->formatDate($stay['requestDate']);

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
        $content .= $this->formatDate($appeal['appealDate']);

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
                        'content' => $this->generateStatusMarkerContent(
                            $data['statusData']['id'],
                            $data['statusRuleData']
                        ),
                        'data' => $this->generateStatusMarkerData(),
                        'style' => self::MARKER_STYLE_DANGER,
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
            'statusRuleData' => $this->getLicenceStatusRule(),
        ];

        return $data;
    }

    /**
     * Generates Status marker content
     *
     * @return string
     */
    protected function generateStatusMarkerContent($statusId, $statusRule)
    {
        $content = '';

        switch ($statusId) {
            case LicenceEntityService::LICENCE_STATUS_CURTAILED:
                $content = "Date of curtailment\n";
                $column = 'curtailedDate';
                break;
            case LicenceEntityService::LICENCE_STATUS_REVOKED:
                $content = "Date of revocation\n";
                $column = 'revokedDate';
                break;
            case LicenceEntityService::LICENCE_STATUS_SUSPENDED:
                $content = "Date of suspension\n";
                $column = 'suspendedDate';
                break;
        }

        if (isset($statusRule['startDate'])) {
            $content .= $this->formatDate($statusRule['startDate']);
            if (isset($statusRule['endDate'])) {
                $content .= " to " . $this->formatDate($statusRule['endDate']);
            }
        } elseif (empty($statusRule)) {
            $content .= $this->formatDate($this->getLicence()[$column]);
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
        return [];
    }


    /**
     * Generate the Status markers
     *
     * @param array $data
     * @return array
     */
    protected function generateStatusRuleMarkers($data)
    {
        $marker = [];
        if (!empty($data['statusRuleData'])) {

            $markerStatuses =  [
                LicenceEntityService::LICENCE_STATUS_SUSPENDED,
                LicenceEntityService::LICENCE_STATUS_REVOKED,
                LicenceEntityService::LICENCE_STATUS_CURTAILED,
            ];
            if (in_array($data['statusRuleData']['licenceStatus']['id'], $markerStatuses)) {
                array_push(
                    $marker,
                    [
                        'content' => $this->generateStatusRuleMarkerContent(
                            $data['statusRuleData']['licenceStatus'],
                            $data['statusRuleData']
                        ),
                        'data' => $this->generateStatusRuleMarkerData(
                            $data['statusRuleData']['licenceStatus'],
                            $data['statusRuleData']
                        ),
                        'style' => self::MARKER_STYLE_DANGER,
                    ]
                );
            }

        }

        return $marker;
    }

    /**
     * Generates data associated with the content for the marker.
     *
     * @return array
     */
    protected function getStatusRuleMarkerData()
    {
        $licence = $this->getLicence();

        if ($licence['status']['id'] !== LicenceEntityService::LICENCE_STATUS_VALID) {
            return [];
        }

        $licenceStatusRule = $this->getLicenceStatusRule();

        if (empty($licenceStatusRule)) {
            return [];
        }

        $data = [
            'statusRuleData' => $licenceStatusRule,
        ];

        return $data;
    }

    /**
     * Generates Status marker content
     *
     * @param array $status
     * @param array $statusRule
     * @return string
     */
    protected function generateStatusRuleMarkerContent($status, $statusRule)
    {
        $content = "Licence due to be " . lcfirst($status['description']) . "\n";
        $content .= $this->formatDate($statusRule['startDate']);

        if (!empty($statusRule['endDate'])) {
            $content .= " to " . $this->formatDate($statusRule['endDate']);
        }

        $content .= "\n%s"; // placeholder for link

        return $content;
    }

    /**
     * Generates data associated with the content for the marker.
     *
     * @param array|null $licence The licence.
     * @param array|null $status The licence status.
     *
     * @return array
     */
    protected function generateStatusRuleMarkerData($licenceStatus = null, $status = null)
    {
        $routes = [
            LicenceEntityService::LICENCE_STATUS_CURTAILED => 'curtail-licence',
            LicenceEntityService::LICENCE_STATUS_REVOKED => 'revoke-licence',
            LicenceEntityService::LICENCE_STATUS_SUSPENDED => 'suspend-licence'
        ];

        $route = 'licence/' . $routes[$licenceStatus['id']];

        $data[] = [
            'type' => 'url',
            'route' => $route,
            'params' => [
                'licence' => $this->getLicence()['id'],
                'status' => $status['id']
            ],
            'linkText' => 'Update details',
            'class' => 'js-modal-ajax'
        ];
        return $data;
    }

    protected function formatDate($date)
    {
        $dateObj = new \DateTime($date);
        return $dateObj->format(self::DATE_FORMAT);
    }

    /**
     * Create the continuation markers
     *
     * @param array $data Data from getContinuationMarkerData()
     *
     * @return array
     */
    protected function generateContinuationMarkers($data)
    {
        if ($data === null) {
            return;
        }

        $dateTime = new \DateTime($data['continuation']['year'] .'-'. $data['continuation']['month'] .'-01');
        $dateFormatted = $dateTime->format('M Y');
        $content = "Licence continuation\n{$dateFormatted}\n%s";

        $markerData = [
            'type' => 'url',
            'route' => 'licence/update-continuation',
            'params' => [
                'licence' => $data['licence']['id']
            ],
            'linkText' => 'Update details',
            'class' => 'js-modal-ajax'
        ];

        $marker = [
            'content' => $content,
            'style' => self::MARKER_STYLE_DANGER,
            'data' => [$markerData]
        ];

        return [$marker];
    }

    /**
     * Get Continuation Details data
     *
     * @return array
     */
    protected function getContinuationMarkerData()
    {
        return $this->getContinuationDetails();
    }
}
