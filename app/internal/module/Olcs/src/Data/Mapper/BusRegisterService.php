<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

/**
 * Class BusRegisterService Mapper
 * @package Olcs\Data\Mapper
 */
class BusRegisterService implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData['fields'] = $data;

        foreach ($formData['fields'] as $key => $value) {
            if (isset($value['id'])) {
                $formData['fields'][$key] = $value['id'];
            }
        }

        $formData['timetable']['timetableAcceptable'] = $data['timetableAcceptable'];
        $formData['timetable']['mapSupplied'] = $data['mapSupplied'];
        $formData['timetable']['routeDescription'] = $data['routeDescription'];
        $formData['conditions']['trcConditionChecked'] = $data['trcConditionChecked'];
        $formData['conditions']['trcNotes'] = $data['trcNotes'];

        if ((!empty($data['variationReasons']))) {
            $formData['fields']['variationReasonsHtml']
                = implode(', ', array_column($data['variationReasons'], 'description'));
        }

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $commandData = $data['fields'];
        $commandData['trcConditionChecked'] = $data['conditions']['trcConditionChecked'];
        $commandData['trcNotes'] = $data['conditions']['trcNotes'];

        if (!empty($data['timetable']['timetableAcceptable'])) {
            $commandData['timetableAcceptable'] = $data['timetable']['timetableAcceptable'];
            $commandData['mapSupplied'] = $data['timetable']['mapSupplied'];
            $commandData['routeDescription'] = $data['timetable']['routeDescription'];
        }

        //opNotifiedLaPte only exists on the form when using scottish rules and the registration is short notice
        if (!isset($commandData['opNotifiedLaPte'])) {
            $commandData['opNotifiedLaPte'] = 'N';
        }

        return $commandData;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form
     * @param array $errors
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        return $errors;
    }
}
