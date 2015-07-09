<?php

/**
 * Inspection Request
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Data\Mapper;

/**
 * Inspection Request
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InspectionRequest
{
    public static function mapFromResult(array $data)
    {
        if (!isset($data['requestDate']) || !$data['requestDate']) {
            $data['requestDate'] = new \DateTime();
        }
        if (isset($data['application']['licence']['enforcementArea']['name'])) {
            $data['enforcementAreaName'] = $data['application']['licence']['enforcementArea']['name'];
        } elseif (isset($data['licence']['enforcementArea']['name'])) {
            $data['enforcementAreaName'] = $data['licence']['enforcementArea']['name'];
        }
        return [
            'data' => $data
        ];
    }

    public static function mapFromForm(array $data)
    {
        return $data['data'];
    }

    public static function mapFromSingleResult(array $inspectionRequest)
    {
        $data = [
            'data' => [
                'id' => $inspectionRequest['id'],
                'version' => $inspectionRequest['version'],
                'reportType' => $inspectionRequest['reportType']['id'],
                'operatingCentre' => $inspectionRequest['operatingCentre']['id'],
                'inspectorName' => $inspectionRequest['inspectorName'],
                'requestType' => $inspectionRequest['requestType']['id'],
                'requestDate' => $inspectionRequest['requestDate'],
                'dueDate' => $inspectionRequest['dueDate'],
                'returnDate' => $inspectionRequest['returnDate'],
                'resultType' => $inspectionRequest['resultType']['id'],
                'fromDate' => $inspectionRequest['fromDate'],
                'toDate' => $inspectionRequest['toDate'],
                'vehiclesExaminedNo' => $inspectionRequest['vehiclesExaminedNo'],
                'trailersExaminedNo' => $inspectionRequest['trailersExaminedNo'],
                'requestorNotes' => $inspectionRequest['requestorNotes'],
                'inspectorNotes' => $inspectionRequest['inspectorNotes']
            ],
            'enforcementAreaName' => ''
        ];
        if (isset($inspectionRequest['application']['licence']['enforcementArea']['name'])) {
            $data['enforcementAreaName'] = $inspectionRequest['application']['licence']['enforcementArea']['name'];
        } elseif (isset($inspectionRequest['licence']['enforcementArea']['name'])) {
            $data['enforcementAreaName'] = $inspectionRequest['licence']['enforcementArea']['name'];
        }
        return $data;
    }

    public static function mapEnforcementAreaFromLicence(array $data)
    {
        return isset($data['enforcementArea']['name']) ? $data['enforcementArea']['name'] : '';
    }

    public static function mapEnforcementAreaFromApplication(array $data)
    {
        return isset($data['licence']['enforcementArea']['name']) ? $data['licence']['enforcementArea']['name'] : '';
    }

    public static function mapFromErrors($form, array $errors)
    {
        $formMessages = [];
        $fields = [
            'reportType',
            'inspectorName',
            'requestType',
            'requestDate',
            'dueDate',
            'returnDate',
            'resultType',
            'fromDate',
            'toDate',
            'vehiclesExaminedNo',
            'trailersExaminedNo',
            'requestorNotes',
            'inspectorNotes'
        ];
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $message) {
                if (in_array($field, $fields)) {
                    $formMessages['data'][$field][] = $message;
                    unset($errors[$field]);
                }
            }
        }
        $form->setMessages($formMessages);
        return $errors;
    }
}
