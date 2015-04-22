<?php

/**
 * Inspect Request Email View Model
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\View\Model\Email;

use Zend\View\Model\ViewModel;
use Common\Controller\Lva\Adapters\AbstractOperatingCentreAdapter as OperatingCentre;

/**
 * Inspect Request Email View Model
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class InspectionRequest extends ViewModel
{
    protected $terminate = true;
    protected $template = 'email/inspection-request';

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

        if (isset($inspectionRequest['requestDate'])) {
            $requestDate = new \DateTime($inspectionRequest['requestDate']);
            $requestDate = $requestDate->format('d/m/Y H:i:s');
        } else {
            $requestDate = '';
        }

        if (isset($inspectionRequest['dueDate'])) {
            $dueDate = new \DateTime($inspectionRequest['dueDate']);
            $dueDate = $dueDate->format('d/m/Y H:i:s');
        } else {
            $dueDate = '';
        }

        if (isset($inspectionRequest['licence']['expiryDate'])) {
            $expiryDate = new \DateTime($inspectionRequest['licence']['expiryDate']);
            $expiryDate = $expiryDate->format('d/m/Y');
        } else {
            $expiryDate = '';
        }

        $data = [
            'inspectionRequestId' => isset($inspectionRequest['id']) ? $inspectionRequest['id'] : '',

            'currentUserName' => isset($user['loginId']) ? $user['loginId'] : '',

            'currentUserEmail' => isset($user['emailAddress']) ? $user['emailAddress'] : '',

            'inspectionRequestDateRequested' => $requestDate,

            'inspectionRequestNotes' => isset($inspectionRequest['requestorNotes'])
                ? $inspectionRequest['requestorNotes'] : '',

            'inspectionRequestDueDate' => $dueDate,

            'ocAddress' => isset($inspectionRequest['operatingCentre']['address'])
                ? $inspectionRequest['operatingCentre']['address'] : null,

            'inspectionRequestType' => isset($inspectionRequest['requestType']['description'])
                ? $inspectionRequest['requestType']['description'] : '',

            'licenceNumber' => isset($inspectionRequest['licence']['licNo'])
                ? $inspectionRequest['licence']['licNo'] : '',

            'licenceType' => $this->getLicenceType($inspectionRequest, $translator),

            'totAuthVehicles' => $this->getTotAuthVehicles($inspectionRequest),

            'totAuthTrailers' => $this->getTotAuthTrailers($inspectionRequest),

            'numberOfOperatingCentres' => isset($inspectionRequest['licence']['operatingCentres'])
                ? count($inspectionRequest['licence']['operatingCentres'])
                : '',

            'expiryDate' => $expiryDate,

            'operatorId' => isset($inspectionRequest['licence']['organisation']['id'])
                ? $inspectionRequest['licence']['organisation']['id']
                : '',

            'operatorName' => isset($inspectionRequest['licence']['organisation']['name'])
                ? $inspectionRequest['licence']['organisation']['name']
                : '',

            'operatorEmail' => isset($inspectionRequest['licence']['correspondenceCd']['emailAddress'])
                ? $inspectionRequest['licence']['correspondenceCd']['emailAddress']
                : '',

            'operatorAddress' => isset($inspectionRequest['licence']['correspondenceCd']['address'])
                ? $inspectionRequest['licence']['correspondenceCd']['address']
                : null,

            'contactPhoneNumbers' => isset($inspectionRequest['licence']['correspondenceCd']['phoneContacts'])
                ? $inspectionRequest['licence']['correspondenceCd']['phoneContacts']
                : null,

            'tradingNames' => $this->getTradingNames($inspectionRequest),

            'workshopIsExternal' => (isset($workshop['isExternal']) && $workshop['isExternal'] === 'Y'),

            'safetyInspectionVehicles' => isset($inspectionRequest['licence']['safetyInsVehicles'])
                ? $inspectionRequest['licence']['safetyInsVehicles']
                : '',

            'safetyInspectionTrailers' => isset($inspectionRequest['licence']['safetyInsTrailers'])
                ? $inspectionRequest['licence']['safetyInsTrailers']
                : '',

            'inspectionProvider' => isset($workshop['contactDetails']) ? $workshop['contactDetails'] : [],

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
        if (!isset($inspectionRequest['licence']['organisation']['licences'])) {
            return [];
        }

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
        if (isset($inspectionRequest['application']['operatingCentres'])) {
            $aocs = array_map(
                function ($aoc) {
                    switch ($aoc['action']) {
                        case OperatingCentre::ACTION_ADDED:
                            $aoc['action'] = 'Added';
                            break;
                        case OperatingCentre::ACTION_UPDATED:
                            $aoc['action'] = 'Updated';
                            break;
                        case OperatingCentre::ACTION_DELETED:
                            $aoc['action'] = 'Deleted';
                            break;
                    }
                    return $aoc;
                },
                $inspectionRequest['application']['operatingCentres']
            );
            return $aocs;
        }
        return [];
    }

    protected function getTradingNames($inspectionRequest)
    {
        $tradingNames = [];
        if (!empty($inspectionRequest['licence']['organisation']['tradingNames'])) {
            $tradingNames = array_map(
                function ($tradingName) {
                    return $tradingName['name'];
                },
                $inspectionRequest['licence']['organisation']['tradingNames']
            );
        }
        return $tradingNames;
    }

    protected function getPeopleFromPeopleData($peopleData)
    {
        $people = [];
        if (isset($peopleData['Results']) && !empty($peopleData['Results'])) {
            $people =  array_map(
                function ($peopleResult) {
                    return $peopleResult['person'];
                },
                $peopleData['Results']
            );
        }
        return $people;
    }
}
