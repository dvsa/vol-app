<?php

namespace Olcs\Data\Mapper\TransportManager;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * EmploymentHistory Mapper
 *
 * @package Olcs\Data\Mapper
 */
class EmploymentHistory implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData = ['transportManager' => $data['transportManager']];
        if (isset($data['id'])) {
            $formData['tm-employer-name-details']['employerName'] = $data['employerName'];
            $formData['tm-employment-details']['position'] = $data['position'];
            $formData['tm-employment-details']['hoursPerWeek'] = $data['hoursPerWeek'];
            $formData['tm-employment-details']['id'] = $data['id'];
            $formData['tm-employment-details']['version'] = $data['version'];
            $formData['address'] = $data['contactDetails']['address'];
        }

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $commandData['id'] = $data['tm-employment-details']['id'];
        $commandData['version'] = $data['tm-employment-details']['version'];
        $commandData['employerName'] = $data['tm-employer-name-details']['employerName'];
        $commandData['position'] = $data['tm-employment-details']['position'];
        $commandData['hoursPerWeek'] = $data['tm-employment-details']['hoursPerWeek'];
        $commandData['address'] = $data['address'];
        if (isset($data['transportManager'])) {
            $commandData['transportManager'] = $data['transportManager'];
        }

        return $commandData;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        return $errors;
    }
}
