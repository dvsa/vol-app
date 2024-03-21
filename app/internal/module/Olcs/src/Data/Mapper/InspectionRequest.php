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

    public static function mapEnforcementAreaFromLicence(array $data)
    {
        return $data['enforcementArea']['name'] ?? '';
    }

    public static function mapEnforcementAreaFromApplication(array $data)
    {
        return $data['licence']['enforcementArea']['name'] ?? '';
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
