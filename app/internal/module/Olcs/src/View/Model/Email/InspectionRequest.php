<?php

/**
 * Inspect Request Email View Model
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\View\Model\Email;

use Zend\View\Model\ViewModel;

/**
 * Inspect Request Email View Model
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class InspectionRequest extends ViewModel
{
    protected $terminate = true;
    protected $template = 'email/inspection-request';

    public function __construct($variables = null, $options = null)
    {
        $initData = [
            'inspectionRequestId' => '',
            'currentUserName' => '',
            'currentUserEmail' => '',
            'inspectionRequestDateRequested' => '',
            'inspectionRequestNotes' => '',
            'inspectionRequestDueDate' => '',
            'ocAddress' => null,
            'inspectionRequestType' => '',
            'licenceNumber' => '',
            'licenceType' => '',
            'totAuthVehicles' => '',
            'totAuthTrailers' => '',
            'numberOfOperatingCentres' => '',
            'expiryDate' => '',
            'operatorId' => '',
            'operatorName' => '',
            'operatorEmail' => '',
            'operatorAddress' => null,
            'contactPhoneNumbers' => null,
            'transportManagers' => [],
            'tradingNames' => [],
            'workshopIsExternal' => false,
            'safetyInspectionVehicles' => '',
            'safetyInspectionTrailers' => '',
            'inspectionProvider' => [],
            'people' => [],
            'otherLicences' => [],
            'applicationOperatingCentres' => [],
        ];

        if (is_array($variables)) {
            $variables = array_merge($initData, $variables);
        } else {
            $variables = $initData;
        }

        return parent::__construct($variables, $options);
    }

    /**
     * Populate the view from entity data
     *
     * @param array $inspectionRequest
     * @param array $user
     * @param array $peopleData
     * @param array $workshop
     * @param \Zend\I18n\Translator\TranslatorInterface $translator
     * @return this
     */
    public function populate($inspectionRequest, $user, $peopleData, $workshops, $translator)
    {
        // use first workshop only
        $workshop = array_shift($workshops);

        $requestDate = new \DateTime($inspectionRequest['requestDate']);
        $requestDate = $requestDate->format(\DATETIMESEC_FORMAT);

        $dueDate = new \DateTime($inspectionRequest['dueDate']);
        $dueDate = $dueDate->format(\DATETIMESEC_FORMAT);

        $expiryDate = new \DateTime($inspectionRequest['licence']['expiryDate']);
        $expiryDate = $expiryDate->format(\DATE_FORMAT);

        $data = [
            'inspectionRequestId' => $inspectionRequest['id'],
            'currentUserName' => $user['loginId'],
            'currentUserEmail' => $user['emailAddress'],
            'inspectionRequestDateRequested' => $requestDate,
            'inspectionRequestNotes' => $inspectionRequest['requestorNotes'],
            'inspectionRequestDueDate' => $dueDate,
            'ocAddress' => $inspectionRequest['operatingCentre']['address'],
            'inspectionRequestType' => $inspectionRequest['requestType']['description'],
            'licenceNumber' => $inspectionRequest['licence']['licNo'],
            'licenceType' => $this->getLicenceType($inspectionRequest, $translator),
            'totAuthVehicles' => $this->getTotAuthVehicles($inspectionRequest),
            'totAuthTrailers' => $this->getTotAuthTrailers($inspectionRequest),
            'numberOfOperatingCentres' => count($inspectionRequest['licence']['operatingCentres']),
            'expiryDate' => $expiryDate,
            'operatorId' =>$inspectionRequest['licence']['organisation']['id'],
            'operatorName' => $inspectionRequest['licence']['organisation']['name'],
            'operatorEmail' => $inspectionRequest['licence']['correspondenceCd']['emailAddress'],
            'operatorAddress' => $inspectionRequest['licence']['correspondenceCd']['address'],
            'contactPhoneNumbers' => $inspectionRequest['licence']['correspondenceCd']['phoneContacts'],
            'transportManagers' => $this->getTransportManagers($inspectionRequest),
            'tradingNames' => $this->getTradingNames($inspectionRequest),
            'workshopIsExternal' => (isset($workshop['isExternal']) && $workshop['isExternal'] === 'Y'),
            'safetyInspectionVehicles' => $inspectionRequest['licence']['safetyInsVehicles'],
            'safetyInspectionTrailers' => $inspectionRequest['licence']['safetyInsTrailers'],
            'inspectionProvider' => $workshop['contactDetails'],
            'people' => $this->getPeopleFromPeopleData($peopleData),
            'otherLicences' => $this->getOtherLicences($inspectionRequest),
            'applicationOperatingCentres' => $this->getApplicationOperatingCentres($inspectionRequest),
        ];

        $this->setVariables($data);

        return $this;
    }

    protected function getTotAuthVehicles($inspectionRequest)
    {
        $totAuthVehicles = '';
        if (!empty($inspectionRequest['application'])) {
            $totAuthVehicles = $inspectionRequest['application']['totAuthVehicles'];
        } elseif (isset($inspectionRequest['licence']['totAuthVehicles'])) {
            $totAuthVehicles = $inspectionRequest['licence']['totAuthVehicles'];
        }
        return $totAuthVehicles;
    }

    protected function getTotAuthTrailers($inspectionRequest)
    {
        $totAuthTrailers = '';
        if (!empty($inspectionRequest['application'])) {
            $totAuthTrailers = $inspectionRequest['application']['totAuthTrailers'];
        } elseif (isset($inspectionRequest['licence']['totAuthTrailers'])) {
            $totAuthTrailers = $inspectionRequest['licence']['totAuthTrailers'];
        }
        return $totAuthTrailers;
    }

    protected function getLicenceType($inspectionRequest, $translator)
    {
        $licenceType = '';
        if (!empty($inspectionRequest['application'])) {
            $licenceType =  $translator->translate($inspectionRequest['application']['licenceType']['id']);
        } elseif (isset($inspectionRequest['licence']['licenceType']['id'])) {
            $licenceType = $translator->translate($inspectionRequest['licence']['licenceType']['id']);
        }
        return $licenceType;
    }

    protected function getOtherLicences($inspectionRequest)
    {
        $licenceNos = array_map(
            function ($licence) {
                return $licence['licNo'];
            },
            $inspectionRequest['licence']['organisation']['licences']
        );

        $currentLicNo = $inspectionRequest['licence']['licNo'];

        $filtered = array_filter(
            $licenceNos,
            function ($licNo) use ($currentLicNo) {
                return ($licNo !== $currentLicNo) && !empty($licNo);
            }
        );

        return array_values($filtered); // ignore keys;
    }

    protected function getApplicationOperatingCentres($inspectionRequest)
    {
        if (!is_array($inspectionRequest['application']['operatingCentres'])) {
            return [];
        }
        return array_map(
            function ($aoc) {
                switch ($aoc['action']) {
                    case 'A':
                        $aoc['action'] = 'Added';
                        break;
                    case 'U':
                        $aoc['action'] = 'Updated';
                        break;
                    case 'D':
                        $aoc['action'] = 'Deleted';
                        break;
                }
                return $aoc;
            },
            $inspectionRequest['application']['operatingCentres']
        );
    }

    protected function getTransportManagers($inspectionRequest)
    {
        return array_map(
            function ($tmLicence) {
                $person = $tmLicence['transportManager']['homeCd']['person'];
                return $person['forename'].' '.$person['familyName'];
            },
            $inspectionRequest['licence']['tmLicences']
        );
    }

    protected function getTradingNames($inspectionRequest)
    {
        return array_map(
            function ($tradingName) {
                return $tradingName['name'];
            },
            $inspectionRequest['licence']['organisation']['tradingNames']
        );
    }

    protected function getPeopleFromPeopleData($peopleData)
    {
        return array_map(
            function ($peopleResult) {
                return $peopleResult['person'];
            },
            $peopleData['Results']
        );
    }
}
