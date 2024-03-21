<?php

namespace Olcs\Data\Mapper;

use Common\RefData;

/**
 * Transport Manager Application mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerApplication
{
    /**
     * Map From Result For Table
     *
     * @param array $data Api data
     *
     * @return array
     */
    public static function mapFromResultForTable(array $data)
    {
        return $data['extra']['tmApplications'] ?? [];
    }

    /**
     * Map From Result
     *
     * @param array $data Api data
     *
     * @return array
     */
    public static function mapFromResult(array $data)
    {
        if (isset($data['tmType']['id'])) {
            $details['tmType'] = $data['tmType'];
        }
        if (isset($data['tmApplicationStatus']['id'])) {
            $details['tmApplicationStatus'] = self::mapTmaStatus($data);
        }

        $details['id'] = $data['id'];
        $details['version'] = $data['version'];
        $details['isOwner'] = $data['isOwner'];
        $details['hasUndertakenTraining'] = $data['hasUndertakenTraining'];
        $details['hoursOfWeek']['hoursPerWeekContent']['hoursMon'] = $data['hoursMon'];
        $details['hoursOfWeek']['hoursPerWeekContent']['hoursTue'] = $data['hoursTue'];
        $details['hoursOfWeek']['hoursPerWeekContent']['hoursWed'] = $data['hoursWed'];
        $details['hoursOfWeek']['hoursPerWeekContent']['hoursThu'] = $data['hoursThu'];
        $details['hoursOfWeek']['hoursPerWeekContent']['hoursFri'] = $data['hoursFri'];
        $details['hoursOfWeek']['hoursPerWeekContent']['hoursSat'] = $data['hoursSat'];
        $details['hoursOfWeek']['hoursPerWeekContent']['hoursSun'] = $data['hoursSun'];
        $details['additionalInformation'] = $data['additionalInformation'];

        return [
            'details' => $details,
            'otherLicences' => $data['otherLicences'],
            'application' => $data['application']
        ];
    }

    /**
     * Map From Form
     *
     * @param array $data Form POST Data
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        return [
            'id' => $data['details']['id'],
            'version' => $data['details']['version'],
            'tmType' => $data['details']['tmType'],
            'hasUndertakenTraining' => $data['details']['hasUndertakenTraining'],
            'additionalInformation' => $data['details']['additionalInformation'],
            'hoursMon' => ($data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursMon']) ?: null,
            'hoursTue' => ($data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursTue']) ?: null,
            'hoursWed' => ($data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursWed']) ?: null,
            'hoursThu' => ($data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursThu']) ?: null,
            'hoursFri' => ($data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursFri']) ?: null,
            'hoursSat' => ($data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursSat']) ?: null,
            'hoursSun' => ($data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursSun']) ?: null,
            'tmApplicationStatus' => $data['details']['tmApplicationStatus'],
            'isOwner' => $data['details']['isOwner']
        ];
    }

    /**
     * Mar errors from Api response data
     *
     * @param \Common\Form\Form $form   Form
     * @param array             $errors Api data
     *
     * @return array
     */
    public static function mapFromErrors(\Common\Form\Form $form, array $errors)
    {
        $details = [
            'tmType',
            'additionalInformation',
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

    /**
     * Map TMA statuses which should not be visible to the relevant visible statuses
     *
     * @param array $data
     *
     * @return mixed
     */
    private static function mapTmaStatus(array $data)
    {
        $status = $data['tmApplicationStatus']['id'];

        if (
            $data['tmApplicationStatus']['id'] === RefData::TMA_STATUS_DETAILS_CHECKED ||
            $data['tmApplicationStatus']['id'] === RefData::TMA_STATUS_DETAILS_SUBMITTED
        ) {
            $status = RefData::TMA_STATUS_INCOMPLETE;
        } elseif ($data['tmApplicationStatus']['id'] === RefData::TMA_STATUS_OPERATOR_APPROVED) {
            $status = RefData::TMA_STATUS_TM_SIGNED;
        }

        return $status;
    }
}
