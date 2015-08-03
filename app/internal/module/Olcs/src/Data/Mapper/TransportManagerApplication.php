<?php

/**
 * Transport Manager Application mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Data\Mapper;

/**
 * Transport Manager Application mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerApplication
{
    public static function mapFromResultForTable(array $data)
    {
        return isset($data['extra']['tmApplications']) ? $data['extra']['tmApplications'] : [];
    }

    public static function mapFromResult(array $data)
    {
        $details = [];
        if (isset($data['result'])) {
            $result = $data['result'];
            $operatingCentres = [];
            foreach ($result['operatingCentres'] as $oc) {
                $operatingCentres[] = $oc['id'];
            }
            $details['operatingCentres'] = $operatingCentres;
            if (isset($result['tmType']['id'])) {
                $details['tmType'] = $result['tmType'];
            }
            if (isset($result['tmApplicationStatus']['id'])) {
                $details['tmApplicationStatus'] = $result['tmApplicationStatus']['id'];
            }
            $details['id'] = $result['id'];
            $details['version'] = $result['version'];
            $details['isOwner'] = $result['isOwner'];
            $details['hoursOfWeek']['hoursPerWeekContent']['hoursMon'] = $result['hoursMon'];
            $details['hoursOfWeek']['hoursPerWeekContent']['hoursTue'] = $result['hoursTue'];
            $details['hoursOfWeek']['hoursPerWeekContent']['hoursWed'] = $result['hoursWed'];
            $details['hoursOfWeek']['hoursPerWeekContent']['hoursThu'] = $result['hoursThu'];
            $details['hoursOfWeek']['hoursPerWeekContent']['hoursFri'] = $result['hoursFri'];
            $details['hoursOfWeek']['hoursPerWeekContent']['hoursSat'] = $result['hoursSat'];
            $details['hoursOfWeek']['hoursPerWeekContent']['hoursSun'] = $result['hoursSun'];
            $details['additionalInformation'] = $result['additionalInformation'];
        }
        return [
            'details' => $details,
            'otherLicences' => $data['otherLicences'],
            'application' => $result['application']
        ];
    }

    public static function mapFromForm(array $data)
    {
        return [
            'id' => $data['details']['id'],
            'version' => $data['details']['version'],
            'tmType' => $data['details']['tmType'],
            'additionalInformation' => $data['details']['additionalInformation'],
            'hoursMon' => ($data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursMon']) ?: null,
            'hoursTue' => ($data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursTue']) ?: null,
            'hoursWed' => ($data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursWed']) ?: null,
            'hoursThu' => ($data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursThu']) ?: null,
            'hoursFri' => ($data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursFri']) ?: null,
            'hoursSat' => ($data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursSat']) ?: null,
            'hoursSun' => ($data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursSun']) ?: null,
            'operatingCentres' => $data['details']['operatingCentres'],
            'tmApplicationStatus' => $data['details']['tmApplicationStatus'],
            'isOwner' => $data['details']['isOwner']
        ];
    }

    public static function mapFromErrors($form, array $errors)
    {
        $details = [
            'tmType',
            'additionalInformation',
            'operatingCentres',
        ];
        $hours = [
            'hoursMon',
            'hoursTue',
            'hoursWed',
            'hoursThu',
            'hoursFri',
            'hoursSat',
            'hoursSun'
        ];
        $formMessages = [];
        foreach ($errors as $field => $message) {
            if (in_array($field, $details)) {
                $formMessages['details'][$field][] = $message;
                unset($errors[$field]);
            }
            if (in_array($field, $hours)) {
                $formMessages['hoursOfWeek']['hoursPerWeekContent'][$field][] = $message;
                unset($errors[$field]);
            }
        }
        $form->setMessages($formMessages);
        return $errors;
    }
}
