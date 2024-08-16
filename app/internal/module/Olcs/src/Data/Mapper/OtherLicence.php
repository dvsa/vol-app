<?php

/**
 * Other Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Olcs\Data\Mapper;

/**
 * Other Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OtherLicence
{
    public static function mapFromResult(array $data)
    {
        return [
            'data' => $data
        ];
    }

    public static function mapFromForm(array $data)
    {
        $res = [
            'licNo' => $data['data']['licNo'],
            'role'  => $data['data']['role'],
            'operatingCentres' => $data['data']['operatingCentres'],
            'totalAuthVehicles' => $data['data']['totalAuthVehicles'],
            'hoursPerWeek' => $data['data']['hoursPerWeek']
        ];
        if (isset($data['data']['id'])) {
            $res['id'] = $data['data']['id'];
        }
        if (isset($data['data']['version'])) {
            $res['version'] = $data['data']['version'];
        }
        if ($data['data']['redirectAction'] == 'edit-tm-application') {
            $res['tmaId'] = $data['data']['redirectId'];
        } else {
            $res['tmlId'] = $data['data']['redirectId'];
        }
        return $res;
    }

    public static function mapFromErrors($form, $errors)
    {
        $details = [
            'licNo',
            'role',
            'operatingCentre',
            'totalAuthVehicles',
            'hoursPerWeek'
        ];
        $formMessages = [];
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $message) {
                if (in_array($field, $details)) {
                    $formMessages['data'][$field][] = $message;
                    unset($errors[$field]);
                }
            }
        }
        $form->setMessages($formMessages);
        return $errors;
    }
}
