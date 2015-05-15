<?php

namespace Olcs\Service\Marker;

use Olcs\Service\Marker\Markers;
use Common\Service\Entity\TmQualificationEntityService;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Entity\TransportManagerEntityService;

/**
 * Class TransportManagerMarkers service. Used to contain business logic for generating markers
 * @package Olcs\Service
 */
class TransportManagerMarkers extends Markers
{
    protected $licenceStatuses = [
        LicenceEntityService::LICENCE_STATUS_SUSPENDED,
        LicenceEntityService::LICENCE_STATUS_VALID,
        LicenceEntityService::LICENCE_STATUS_CURTAILED
    ];

    protected $applicationStatuses = [
        ApplicationEntityService::APPLICATION_STATUS_GRANTED,
        ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION
    ];

    protected $tmStatuses = [
        TransportManagerEntityService::TRANSPORT_MANAGER_TYPE_EXTERNAL,
        TransportManagerEntityService::TRANSPORT_MANAGER_TYPE_BOTH
    ];

    protected $qualificationGbStatuses = [
        TmQualificationEntityService::QUALIFICATION_TYPE_AR,
        TmQualificationEntityService::QUALIFICATION_TYPE_CPCSI,
        TmQualificationEntityService::QUALIFICATION_TYPE_EXSI
    ];

    protected $qualificationNiStatuses = [
        TmQualificationEntityService::QUALIFICATION_TYPE_NIAR,
        TmQualificationEntityService::QUALIFICATION_TYPE_NICPCSI,
        TmQualificationEntityService::QUALIFICATION_TYPE_NIEXSI
    ];

    protected $totalOperators = [];

    protected $totalVehicles = [];

    /**
     * Gets the data required to generate the transport manager markers.
     *
     * @return array
     */
    protected function getTransportManagerMarkerData()
    {
        $tm = $this->getTransportManager();
        return [
            'transportManagerData' => isset($tm['transportManager']) ? $tm['transportManager'] : [],
        ];
    }

    /**
     * Gets the data required to generate the licence transport managers markers.
     *
     * @return array
     */
    protected function getLicenceTransportManagersMarkerData()
    {
        $tm = $this->getLicenceTransportManagers();
        return [
            'licenceTransportManagersData' =>
                isset($tm['licenceTransportManagers']) ? $tm['licenceTransportManagers'] : [],
        ];
    }

    /**
     * Gets the data required to generate the application transport managers markers.
     *
     * @return array
     */
    protected function getApplicationTransportManagersMarkerData()
    {
        $tm = $this->getApplicationTransportManagers();
        return [
            'applicationTransportManagersData' =>
                isset($tm['applicationTransportManagers']) ? $tm['applicationTransportManagers'] : [],
        ];
    }

    /**
     * Generate the transport manager markers
     *
     * @param array $data
     * @return array
     */
    protected function generateTransportManagerMarkers($data)
    {
        $markers = [];
        if (!empty($data['transportManagerData'])) {
            if ($this->checkForGbSiQualification($data['transportManagerData'])) {
                array_push(
                    $markers,
                    [
                        'content' => $this->generateQualificationsMarkerContent('GB'),
                        'data' => []
                    ]
                );
            }
            if ($this->checkForNiSiQualification($data['transportManagerData'])) {
                array_push(
                    $markers,
                    [
                        'content' => $this->generateQualificationsMarkerContent('NI'),
                        'data' => []
                    ]
                );
            }
            $tmId = $data['transportManagerData']['id'];
            if ($this->checkForRule450($data['transportManagerData'], $tmId)) {
                array_push(
                    $markers,
                    [
                        'content' => $this->generateRule450MarkerContent(
                            $this->totalOperators[$tmId],
                            $this->totalVehicles[$tmId]
                        ),
                        'data' => []
                    ]
                );
            }
        }
        return $markers;
    }

    /**
     * Generate the licence transport manager markers
     *
     * @param array $data
     * @return array
     */
    protected function generateLicenceTransportManagersMarkers($data)
    {
        return $this->generateLicenceOrApplicationTmMarkers(
            $data,
            'licenceTransportManagersData',
            'checkForGbSiQualificationTmLicence',
            'checkForNiSiQualificationTmLicence'
        );
    }

    /**
     * Generate the application transport managers markers
     *
     * @param array $data
     * @return array
     */
    protected function generateApplicationTransportManagersMarkers($data)
    {
        return $this->generateLicenceOrApplicationTmMarkers(
            $data,
            'applicationTransportManagersData',
            'checkForGbSiQualificationTmApplication',
            'checkForNiSiQualificationTmApplication'
        );
    }

    /**
     * Generate the application transport managers markers
     *
     * @param array $data
     * @param string $key
     * @param string $gbMethod
     * @param string $niMethod
     * @return array
     */
    protected function generateLicenceOrApplicationTmMarkers($data, $key, $gbMethod, $niMethod)
    {
        $markers = [];
        if (!empty($data[$key])) {
            foreach ($data[$key] as $record) {
                $name = $record['transportManager']['homeCd']['person']['forename'] . ' ' .
                    $record['transportManager']['homeCd']['person']['familyName'];
                $tmId = $record['transportManager']['id'];

                if ($this->$gbMethod($record)) {
                    array_push(
                        $markers,
                        [
                            'content' => $this->generateQualificationsMarkerContent('GB', $name),
                            'data' => []
                        ]
                    );
                }
                if ($this->$niMethod($record)) {
                    array_push(
                        $markers,
                        [
                            'content' => $this->generateQualificationsMarkerContent('NI', $name),
                            'data' => []
                        ]
                    );
                }
                if ($this->checkForRule450($record['transportManager'], $tmId)) {
                    array_push(
                        $markers,
                        [
                            'content' => $this->generateRule450MarkerContent(
                                $this->totalOperators[$tmId],
                                $this->totalVehicles[$tmId],
                                $name
                            ),
                            'data' => []
                        ]
                    );
                }
            }
        }
        return $markers;
    }

    /**
     * Business logic to determine if we should display marker for GB SI qualification
     *
     * @param array $data
     * @return array
     */
    protected function checkForGbSiQualification($data)
    {
        return $this->checkForQualifications($data, $this->qualificationGbStatuses, 'N');
    }

    /**
     * Business logic to determine if we should display marker for NI SI qualification
     *
     * @return array
     */
    protected function checkForNiSiQualification($data)
    {
        return $this->checkForQualifications($data, $this->qualificationNiStatuses, 'Y');
    }

    /**
     * Business logic to determine if we should display marker for GB SI qualification
     *
     * @param array $data
     * @return array
     */
    protected function checkForGbSiQualificationTmLicence($data)
    {
        return $this->checkForQualificationTmLicence($data, $this->qualificationGbStatuses, 'N');
    }

    /**
     * Business logic to determine if we should display marker for NI SI qualification
     *
     * @return array
     */
    protected function checkForNiSiQualificationTmLicence($data)
    {
        return $this->checkForQualificationTmLicence($data, $this->qualificationNiStatuses, 'Y');
    }

    /**
     * Business logic to determine if we should display marker for GB SI qualification
     *
     * @param array $data
     * @return array
     */
    protected function checkForGbSiQualificationTmApplication($data)
    {
        return $this->checkForQualificationTmApplication($data, $this->qualificationGbStatuses, 'N');
    }

    /**
     * Business logic to determine if we should display marker for NI SI qualification
     *
     * @return array
     */
    protected function checkForNiSiQualificationTmApplication($data)
    {
        return $this->checkForQualificationTmApplication($data, $this->qualificationNiStatuses, 'Y');
    }

    /**
     * Common business logic to determine if we should display marker for SI qualification
     *
     * @return bool
     */
    protected function checkForQualifications($data, $qualificationStatuses, $niFlag)
    {
        if (!in_array($data['tmType']['id'], $this->tmStatuses)) {
            return false;
        }
        $checkQualifications = false;
        foreach ($data['tmLicences'] as $tmLicence) {
            if ($this->checkLicenceRulesForQualifications($tmLicence['licence'], $niFlag)) {
                $checkQualifications = true;
                break;
            }
        }
        if (!$checkQualifications) {
            foreach ($data['tmApplications'] as $tmApplication) {
                if ($this->checkApplicationRulesForQualifications($tmApplication['application'], $niFlag)) {
                    $checkQualifications = true;
                    break;
                }
            }
        }
        if ($checkQualifications) {
            foreach ($data['qualifications'] as $qualification) {
                if (in_array($qualification['qualificationType']['id'], $qualificationStatuses)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Common business logic to determine if we should display marker for SI qualification
     *
     * @param array $data
     * @param array $qualificationStatuses
     * @param string $niFlag
     * @return bool
     */
    protected function checkForQualificationTmLicence($data, $qualificationStatuses, $niFlag)
    {
        return $this->checkForQualificationTmLicenceOrApplication(
            $data,
            $qualificationStatuses,
            $niFlag,
            'checkLicenceRulesForQualifications',
            'licence'
        );
    }

    /**
     * Common business logic to determine if we should display marker for SI qualification
     *
     * @param array $data
     * @param array $qualificationStatuses
     * @param string $niFlag
     * @return bool
     */
    protected function checkForQualificationTmApplication($data, $qualificationStatuses, $niFlag)
    {
        return $this->checkForQualificationTmLicenceOrApplication(
            $data,
            $qualificationStatuses,
            $niFlag,
            'checkApplicationRulesForQualifications',
            'application'
        );
    }

    /**
     * Common business logic to determine if we should display marker for SI qualification
     *
     * @param array $data
     * @param array $qualificationStatuses
     * @param string $niFlag
     * @param string $checkMethod
     * @param string $key
     * @return bool
     */
    protected function checkForQualificationTmLicenceOrApplication(
        $data,
        $qualificationStatuses,
        $niFlag,
        $checkMethod,
        $key
    ) {
        if (!in_array($data['transportManager']['tmType']['id'], $this->tmStatuses)) {
            return false;
        }
        if ($this->$checkMethod($data[$key], $niFlag)) {
            foreach ($data['transportManager']['qualifications'] as $qualification) {
                if (in_array($qualification['qualificationType']['id'], $qualificationStatuses)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Business logic to determine if we should display marker for rule 4/50
     *
     * @param array $data
     * @return array
     */
    protected function checkForRule450($data, $tmId)
    {
        $operators = [];
        $this->totalVehicles[$tmId] = 0;
        foreach ($data['tmLicences'] as $tmLicence) {
            if ($this->checkLicenceRulesForRule450($tmLicence['licence'])) {

                if (!in_array($tmLicence['licence']['organisation']['id'], $operators)) {
                    $operators[] = $tmLicence['licence']['organisation']['id'];
                }

                $this->totalVehicles[$tmId] += $tmLicence['licence']['totAuthVehicles'];
            }
        }
        foreach ($data['tmApplications'] as $tmApplication) {
            if ($this->checkApplicationRulesForRule450($tmApplication['application'])) {

                if (!in_array($tmApplication['application']['licence']['organisation']['id'], $operators)) {
                    $operators[] = $tmApplication['application']['licence']['organisation']['id'];
                }

                $this->totalVehicles[$tmId] += $tmApplication['application']['totAuthVehicles'];
            }
        }
        $this->totalOperators[$tmId] = count($operators);
        return ($this->totalOperators[$tmId] > 4 || $this->totalVehicles[$tmId] > 50) ? true : false;
    }

    /**
     * Check licence rules for qualifications markers
     *
     * @param int $licence
     * @param string $niFlag
     * @return array
     */
    protected function checkLicenceRulesForQualifications($licence, $niFlag)
    {
        if ($licence['licenceType']['id'] ==
            LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL &&
            $licence['niFlag'] === $niFlag &&
            in_array($licence['status']['id'], $this->licenceStatuses)) {
            return true;
        }
        return false;
    }

    /**
     * Check applications rules for qualifications markers
     *
     * @param int $licence
     * @param string $niFlag
     * @return array
     */
    protected function checkApplicationRulesForQualifications($application, $niFlag)
    {
        if ($application['licenceType']['id'] ==
            LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL &&
            !$application['isVariation'] &&
            $application['niFlag'] == $niFlag &&
            in_array($application['status']['id'], $this->applicationStatuses)) {
            return true;
        }
        return false;
    }

    /**
     * Check licence rules for rule 450 markers
     *
     * @param int $licence
     * @return array
     */
    protected function checkLicenceRulesForRule450($licence)
    {
        if ($licence['licenceType']['id'] ==
            LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL &&
            in_array($licence['status']['id'], $this->licenceStatuses)) {
            return true;
        }
        return false;
    }

    /**
     * Check application rules for rule 450 markers
     *
     * @param int $application
     * @return array
     */
    protected function checkApplicationRulesForRule450($application)
    {
        if ($application['licenceType']['id'] ==
            LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL &&
            !$application['isVariation'] &&
            in_array($application['status']['id'], $this->applicationStatuses)) {
            return true;
        }
        return false;
    }

    /**
     * Generates rule 4/50 marker content
     *
     * @param int $operators
     * @param int $vehicles
     * @param string $name
     * @return string
     */
    protected function generateRule450MarkerContent($operators, $vehicles, $name = '')
    {
        $content = ($name ? $name . "\n" : '') . "4/50 limit exceeded\n";
        $content .= "$operators operators / $vehicles vehicles";
        return $content;
    }

    /**
     * Generates qualifications marker content
     *
     * @param string $type
     * @param string $name
     * @return string
     */
    protected function generateQualificationsMarkerContent($type, $name = '')
    {
        if ($type == 'GB') {
            $content = ($name ? $name . "\n" : "") . "GB SI qualification required";
        } else {
            $content = ($name ? $name . "\n" : "") . "NI SI qualification required";
        }
        return $content;
    }
}
